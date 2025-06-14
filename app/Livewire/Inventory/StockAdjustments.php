<?php

namespace App\Livewire\Inventory;

use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class StockAdjustments extends Component
{
    use WithPagination;
    use Toast;

    public $showAdjustmentModal = false;
    public $showBulkModal = false;

    // Single adjustment form
    public $selectedProduct = '';
    public $selectedWarehouse = '';
    public $adjustmentType = 'in';
    public $quantity = '';
    public $reason = '';
    public $notes = '';

    // Bulk adjustment
    public array $bulkAdjustments = [];

    // Search and filters
    public $search = '';
    public $warehouseFilter = '';
    public $typeFilter = '';
    public $dateFilter = '';

    protected array $rules = [
        'selectedProduct' => 'required|exists:products,id',
        'selectedWarehouse' => 'required|exists:warehouses,id',
        'adjustmentType' => 'required|in:in,out',
        'quantity' => 'required|integer|min:1',
        'reason' => 'required|string|max:255',
        'notes' => 'nullable|string',
    ];

    public function render()
    {
        $adjustments = StockMovement::with(['product', 'warehouse', 'user'])
            ->where('type', 'adjustment')
            ->when($this->search, fn($q) => $q->whereHas('product', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            }))
            ->when($this->warehouseFilter, fn($q) => $q->where('warehouse_id', $this->warehouseFilter))
            ->when($this->typeFilter, function ($q) {
                if ($this->typeFilter === 'positive') {
                    return $q->where('quantity_changed', '>', 0);
                } elseif ($this->typeFilter === 'negative') {
                    return $q->where('quantity_changed', '<', 0);
                }
            })
            ->when($this->dateFilter, function ($q) {
                switch ($this->dateFilter) {
                    case 'today':
                        return $q->whereDate('created_at', today());
                    case 'week':
                        return $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    case 'month':
                        return $q->whereMonth('created_at', now()->month);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $products = Product::where('status', 'active')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $filterOptions = [
            'warehouses' => $warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name]),
            'types' => [
                ['value' => '', 'label' => 'All Adjustments'],
                ['value' => 'positive', 'label' => 'Stock Increases'],
                ['value' => 'negative', 'label' => 'Stock Decreases'],
            ],
            'dates' => [
                ['value' => '', 'label' => 'All Time'],
                ['value' => 'today', 'label' => 'Today'],
                ['value' => 'week', 'label' => 'This Week'],
                ['value' => 'month', 'label' => 'This Month'],
            ]
        ];

        return view('livewire.inventory.stock-adjustments', [
            'adjustments' => $adjustments,
            'products' => $products,
            'warehouses' => $warehouses,
            'filterOptions' => $filterOptions,
        ])->layout('layouts.app', ['title' => 'Stock Adjustments']);
    }

    public function openAdjustmentModal()
    {
        $this->resetAdjustmentForm();
        $this->showAdjustmentModal = true;
        $this->resetValidation();
    }

    public function openBulkModal()
    {
        $this->bulkAdjustments = [];
        $this->showBulkModal = true;
    }

    public function addBulkAdjustment()
    {
        $this->bulkAdjustments[] = [
            'product_id' => '',
            'warehouse_id' => '',
            'type' => 'in',
            'quantity' => '',
            'reason' => '',
        ];
    }

    public function removeBulkAdjustment($index)
    {
        unset($this->bulkAdjustments[$index]);
        $this->bulkAdjustments = array_values($this->bulkAdjustments);
    }

    public function processAdjustment()
    {
        $this->validate();

        try {
            $product = Product::find($this->selectedProduct);
            $inventory = Inventory::where('product_id', $this->selectedProduct)
                ->where('warehouse_id', $this->selectedWarehouse)
                ->first();

            if (!$inventory) {
                // Create inventory record if doesn't exist
                $inventory = Inventory::create([
                    'product_id' => $this->selectedProduct,
                    'warehouse_id' => $this->selectedWarehouse,
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'average_cost' => $product->cost_price,
                ]);
            }

            $oldQuantity = $inventory->quantity_on_hand;
            $changeQuantity = $this->adjustmentType === 'in' ? $this->quantity : -$this->quantity;
            $newQuantity = $oldQuantity + $changeQuantity;

            // Prevent negative quantities
            if ($newQuantity < 0) {
                $this->error('Adjustment would result in negative stock. Available: ' . $oldQuantity);
                return;
            }

            // Update inventory
            $inventory->update([
                'quantity_on_hand' => $newQuantity,
                'last_counted_at' => now(),
            ]);

            // Create stock movement record
            StockMovement::create([
                'product_id' => $this->selectedProduct,
                'warehouse_id' => $this->selectedWarehouse,
                'type' => 'adjustment',
                'quantity_before' => $oldQuantity,
                'quantity_changed' => $changeQuantity,
                'quantity_after' => $newQuantity,
                'unit_cost' => $inventory->average_cost,
                'user_id' => auth()->id(),
                'notes' => $this->reason . ($this->notes ? ' - ' . $this->notes : ''),
            ]);

            $this->success('Stock adjustment processed successfully!');
            $this->showAdjustmentModal = false;
            $this->resetAdjustmentForm();
        } catch (\Exception $e) {
            $this->error('Error processing adjustment: ' . $e->getMessage());
        }
    }

    public function processBulkAdjustments()
    {
        if (empty($this->bulkAdjustments)) {
            $this->error('Please add at least one adjustment.');
            return;
        }

        $processedCount = 0;
        $errors = [];

        foreach ($this->bulkAdjustments as $index => $adjustment) {
            try {
                if (
                    empty($adjustment['product_id']) || empty($adjustment['warehouse_id']) ||
                    empty($adjustment['quantity']) || empty($adjustment['reason'])
                ) {
                    continue;
                }

                $product = Product::find($adjustment['product_id']);
                $inventory = Inventory::firstOrCreate([
                    'product_id' => $adjustment['product_id'],
                    'warehouse_id' => $adjustment['warehouse_id'],
                ], [
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'average_cost' => $product->cost_price,
                ]);

                $oldQuantity = $inventory->quantity_on_hand;
                $changeQuantity = $adjustment['type'] === 'in' ? $adjustment['quantity'] : -$adjustment['quantity'];
                $newQuantity = $oldQuantity + $changeQuantity;

                if ($newQuantity < 0) {
                    $errors[] = "Row " . ($index + 1) . ": Would result in negative stock";
                    continue;
                }

                $inventory->update([
                    'quantity_on_hand' => $newQuantity,
                    'last_counted_at' => now(),
                ]);

                StockMovement::create([
                    'product_id' => $adjustment['product_id'],
                    'warehouse_id' => $adjustment['warehouse_id'],
                    'type' => 'adjustment',
                    'quantity_before' => $oldQuantity,
                    'quantity_changed' => $changeQuantity,
                    'quantity_after' => $newQuantity,
                    'unit_cost' => $inventory->average_cost,
                    'user_id' => auth()->id(),
                    'notes' => 'Bulk adjustment: ' . $adjustment['reason'],
                ]);

                $processedCount++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        if ($processedCount > 0) {
            $this->success("{$processedCount} adjustments processed successfully!");
        }

        if (!empty($errors)) {
            $this->error('Some adjustments failed: ' . implode(', ', $errors));
        }

        if ($processedCount > 0 && empty($errors)) {
            $this->showBulkModal = false;
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'warehouseFilter', 'typeFilter', 'dateFilter']);
    }

    private function resetAdjustmentForm()
    {
        $this->reset(['selectedProduct', 'selectedWarehouse', 'adjustmentType', 'quantity', 'reason', 'notes']);
        $this->adjustmentType = 'in';
    }
}
