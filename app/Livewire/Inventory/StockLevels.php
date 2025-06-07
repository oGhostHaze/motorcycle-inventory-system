<?php

namespace App\Livewire\Inventory;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class StockLevels extends Component
{
    use WithPagination;
    use Toast;

    public bool $showAdjustmentModal = false;
    public ?Inventory $selectedInventory = null;

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
                switch ($this->stockFilter) {
                    case 'low':
                        return $q->whereHas('product', function ($query) {
                            $query->whereRaw('inventories.quantity_on_hand <= products.min_stock_level');
                        });
                    case 'out':
                        return $q->where('quantity_on_hand', 0);
                    case 'over':
                        return $q->whereHas('product', function ($query) {
                            $query->whereRaw('inventories.quantity_on_hand > products.max_stock_level');
                        });
                    case 'good':
                        return $q->whereHas('product', function ($query) {
                            $query->whereRaw('inventories.quantity_on_hand > products.min_stock_level')
                                ->whereRaw('inventories.quantity_on_hand <= products.max_stock_level');
                        });
                }
            })
            ->when($this->statusFilter, fn($q) => $q->whereHas('product', function ($query) {
                $query->where('status', $this->statusFilter);
            }))
            ->orderBy('quantity_on_hand')
            ->paginate(24);

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $filterOptions = [
            'warehouses' => $warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name]),
            'categories' => $categories->map(fn($c) => ['value' => $c->id, 'label' => $c->name]),
            'stock' => [
                ['value' => '', 'label' => 'All Stock Levels'],
                ['value' => 'out', 'label' => 'Out of Stock'],
                ['value' => 'low', 'label' => 'Low Stock'],
                ['value' => 'good', 'label' => 'Good Stock'],
                ['value' => 'over', 'label' => 'Overstock'],
            ],
            'status' => [
                ['value' => '', 'label' => 'All Products'],
                ['value' => 'active', 'label' => 'Active Only'],
                ['value' => 'inactive', 'label' => 'Inactive Only'],
                ['value' => 'discontinued', 'label' => 'Discontinued'],
            ]
        ];

        // Calculate summary stats
        $totalItems = Inventory::sum('quantity_on_hand');
        $totalValue = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(inventories.quantity_on_hand * products.cost_price) as total')
            ->value('total') ?? 0;
        $lowStockCount = Inventory::whereHas('product', function ($query) {
            $query->whereRaw('inventories.quantity_on_hand <= products.min_stock_level');
        })->count();
        $outOfStockCount = Inventory::where('quantity_on_hand', 0)->count();

        return view('livewire.inventory.stock-levels', [
            'inventory' => $inventory,
            'filterOptions' => $filterOptions,
            'totalItems' => $totalItems,
            'totalValue' => $totalValue,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
        ])->layout('layouts.app', ['title' => 'Stock Levels']);
    }

    public function openAdjustmentModal(Inventory $inventory)
    {
        $this->selectedInventory = $inventory;
        $this->adjustment_quantity = '';
        $this->adjustment_type = 'in';
        $this->adjustment_reason = '';
        $this->adjustment_notes = '';
        $this->showAdjustmentModal = true;
        $this->resetValidation();
    }

    public function processAdjustment()
    {
        $this->validate([
            'adjustment_quantity' => 'required|integer|min:1',
            'adjustment_type' => 'required|in:in,out',
            'adjustment_reason' => 'required|string|max:255',
            'adjustment_notes' => 'nullable|string',
        ]);

        try {
            $oldQuantity = $this->selectedInventory->quantity_on_hand;
            $changeQuantity = $this->adjustment_type === 'in'
                ? $this->adjustment_quantity
                : -$this->adjustment_quantity;

            $newQuantity = $oldQuantity + $changeQuantity;

            // Prevent negative quantities
            if ($newQuantity < 0) {
                $this->error('Cannot reduce stock below zero. Available quantity: ' . $oldQuantity);
                return;
            }

            // Update inventory
            $this->selectedInventory->update([
                'quantity_on_hand' => $newQuantity,
                'last_counted_at' => now(),
            ]);

            // Create stock movement record
            $this->selectedInventory->product->stockMovements()->create([
                'warehouse_id' => $this->selectedInventory->warehouse_id,
                'type' => 'adjustment',
                'quantity_before' => $oldQuantity,
                'quantity_changed' => $changeQuantity,
                'quantity_after' => $newQuantity,
                'unit_cost' => $this->selectedInventory->average_cost,
                'user_id' => auth()->id(),
                'notes' => $this->adjustment_reason . ($this->adjustment_notes ? ' - ' . $this->adjustment_notes : ''),
            ]);

            $this->success('Stock adjustment completed successfully!');
            $this->showAdjustmentModal = false;
        } catch (\Exception $e) {
            $this->error('Error processing adjustment: ' . $e->getMessage());
        }
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'warehouseFilter', 'categoryFilter', 'stockFilter', 'statusFilter']);
    }

    public function refreshData()
    {
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Stock levels refreshed!'
        ]);
    }

    public function getStockStatusClass($inventory)
    {
        $quantity = $inventory->quantity_on_hand;
        $minLevel = $inventory->product->min_stock_level ?? 0;
        $maxLevel = $inventory->product->max_stock_level ?? 999999;

        if ($quantity == 0) return 'error';
        if ($quantity <= $minLevel) return 'warning';
        if ($quantity > $maxLevel) return 'info';
        return 'success';
    }

    public function getStockStatusText($inventory)
    {
        $quantity = $inventory->quantity_on_hand;
        $minLevel = $inventory->product->min_stock_level ?? 0;
        $maxLevel = $inventory->product->max_stock_level ?? 999999;

        if ($quantity == 0) return 'Out of Stock';
        if ($quantity <= $minLevel) return 'Low Stock';
        if ($quantity > $maxLevel) return 'Overstock';
        return 'Good Stock';
    }
}
