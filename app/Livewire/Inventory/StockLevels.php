<?php

namespace App\Livewire\Inventory;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class StockLevels extends Component
{
    use WithPagination;
    use Toast;

    public bool $showAdjustmentModal = false;
    public bool $showDetailsModal = false; // Add this property
    public ?Inventory $selectedInventory = null;
    public ?Product $selectedProduct = null; // Add this property

    // Adjustment form fields
    public $adjustment_quantity = '';
    public string $adjustment_type = 'in';
    public string $adjustment_reason = '';
    public string $adjustment_notes = '';

    // Search and filters
    public string $search = '';
    public string $warehouseFilter = '';
    public string $categoryFilter = '';
    public string $stockFilter = '';
    public string $statusFilter = '';

    // View options
    public string $viewMode = 'grid'; // grid or table

    public function render()
    {
        $inventory = Inventory::with(['product.category', 'warehouse'])
            ->when($this->search, fn($q) => $q->whereHas('product', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            }))
            ->when($this->warehouseFilter, fn($q) => $q->where('warehouse_id', $this->warehouseFilter))
            ->when($this->categoryFilter, fn($q) => $q->whereHas('product', function ($query) {
                $query->where('category_id', $this->categoryFilter);
            }))
            ->when($this->stockFilter, function ($q) {
                if ($this->stockFilter === 'low') {
                    $q->whereHas('product', function ($query) {
                        $query->whereColumn('inventories.quantity_available', '<=', 'products.min_stock_level')
                            ->whereNotNull('products.min_stock_level');
                    });
                } elseif ($this->stockFilter === 'out') {
                    $q->where('quantity_available', '<=', 0);
                } elseif ($this->stockFilter === 'in_stock') {
                    $q->where('quantity_available', '>', 0);
                }
            })
            ->when($this->statusFilter, fn($q) => $q->whereHas('product', function ($query) {
                $query->where('status', $this->statusFilter);
            }))
            ->orderBy('quantity_available', 'asc')
            ->paginate(20);

        // Calculate summary stats
        $totalItems = Inventory::sum('quantity_on_hand');
        $totalValue = Inventory::with('product')
            ->get()
            ->sum(function ($item) {
                return $item->quantity_on_hand * $item->product->cost_price;
            });
        $lowStockCount = Inventory::whereHas('product', function ($query) {
            $query->whereColumn('inventories.quantity_available', '<=', 'products.min_stock_level')
                ->whereNotNull('products.min_stock_level');
        })->count();
        $outOfStockCount = Inventory::where('quantity_available', '<=', 0)->count();

        // Filter options
        $filterOptions = [
            'warehouses' => Warehouse::where('is_active', true)
                ->get(['id as value', 'name as label'])
                ->toArray(),
            'categories' => Category::get(['id as value', 'name as label'])
                ->toArray(),
            'stock' => [
                ['value' => '', 'label' => 'All Stock Levels'],
                ['value' => 'in_stock', 'label' => 'In Stock'],
                ['value' => 'low', 'label' => 'Low Stock'],
                ['value' => 'out', 'label' => 'Out of Stock'],
            ],
            'status' => [
                ['value' => '', 'label' => 'All Status'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
                ['value' => 'discontinued', 'label' => 'Discontinued'],
            ]
        ];

        return view('livewire.inventory.stock-levels', [
            'inventory' => $inventory,
            'totalItems' => $totalItems,
            'totalValue' => $totalValue,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'filterOptions' => $filterOptions,
        ]);
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function refreshData()
    {
        $this->resetPage();
        $this->success('Data refreshed!');
    }

    public function clearFilters()
    {
        $this->reset(['search', 'warehouseFilter', 'categoryFilter', 'stockFilter', 'statusFilter']);
        $this->resetPage();
    }

    // Add this new method for viewing product details
    public function viewDetails($inventoryId)
    {
        $inventory = Inventory::with([
            'product.category',
            'product.subcategory',
            'product.brand',
            'warehouse'
        ])->find($inventoryId);

        if (!$inventory) {
            $this->error('Inventory item not found.');
            return;
        }

        $this->selectedInventory = $inventory;

        // Load the product with additional details including recent stock movements
        $this->selectedProduct = Product::with([
            'category',
            'subcategory',
            'brand',
            'inventory.warehouse',
            'stockMovements' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }
        ])->find($inventory->product_id);

        $this->showDetailsModal = true;
    }

    // Add this new method for transitioning from details to adjustment modal
    public function openAdjustmentFromDetails()
    {
        if (!$this->selectedInventory) {
            $this->error('No inventory item selected.');
            return;
        }

        // Close the details modal first
        $this->showDetailsModal = false;

        // Reset adjustment form
        $this->resetAdjustmentForm();

        // Small delay to ensure modal transition, then open adjustment modal
        $this->dispatch('open-adjustment-modal');
    }

    public function openAdjustmentModal($inventoryId)
    {
        $this->selectedInventory = Inventory::with(['product', 'warehouse'])->find($inventoryId);

        if (!$this->selectedInventory) {
            $this->error('Inventory item not found.');
            return;
        }

        $this->resetAdjustmentForm();
        $this->showAdjustmentModal = true;
    }

    public function processAdjustment()
    {
        $this->validate([
            'adjustment_quantity' => 'required|integer|min:1',
            'adjustment_type' => 'required|in:in,out',
            'adjustment_reason' => 'required|string|max:255',
        ]);

        try {
            $oldQuantity = $this->selectedInventory->quantity_on_hand;

            if ($this->adjustment_type === 'in') {
                $newQuantity = $oldQuantity + $this->adjustment_quantity;
                $changeQuantity = $this->adjustment_quantity;
            } else {
                $newQuantity = max(0, $oldQuantity - $this->adjustment_quantity);
                $changeQuantity = -$this->adjustment_quantity;
            }

            // Update inventory
            $this->selectedInventory->update([
                'quantity_on_hand' => $newQuantity,
                'quantity_available' => $newQuantity - $this->selectedInventory->quantity_reserved,
            ]);

            // Create stock movement record with correct column names
            StockMovement::create([
                'product_id' => $this->selectedInventory->product_id,
                'warehouse_id' => $this->selectedInventory->warehouse_id,
                'type' => 'adjustment',
                'quantity_before' => $oldQuantity,
                'quantity_changed' => $changeQuantity,
                'quantity_after' => $newQuantity,
                'unit_cost' => $this->selectedInventory->average_cost ?? $this->selectedInventory->product->cost_price,
                'user_id' => auth()->id(),
                'notes' => $this->adjustment_reason . ($this->adjustment_notes ? ' - ' . $this->adjustment_notes : ''),
            ]);

            $this->success('Stock adjustment processed successfully!');
            $this->showAdjustmentModal = false;
            $this->resetAdjustmentForm();
        } catch (\Exception $e) {
            $this->error('Error processing adjustment: ' . $e->getMessage());
        }
    }

    private function resetAdjustmentForm()
    {
        $this->adjustment_quantity = '';
        $this->adjustment_type = 'in';
        $this->adjustment_reason = '';
        $this->adjustment_notes = '';
    }

    public function getStockStatusText($item)
    {
        if ($item->quantity_available <= 0) {
            return 'Out of Stock';
        } elseif ($item->product->min_stock_level && $item->quantity_available <= $item->product->min_stock_level) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    public function getStockStatusClass($item)
    {
        if ($item->quantity_available <= 0) {
            return 'error';
        } elseif ($item->product->min_stock_level && $item->quantity_available <= $item->product->min_stock_level) {
            return 'warning';
        } else {
            return 'success';
        }
    }
}
