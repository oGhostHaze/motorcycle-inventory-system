<?php

namespace App\Livewire\Inventory;

use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;

class WarehouseManagement extends Component
{
    use WithPagination;
    use Toast;

    public $showModal = false;
    public $editMode = false;
    public $selectedWarehouse = null;

    // View Inventory Modal
    public $showInventoryModal = false;
    public $inventoryWarehouse = null;
    public $inventoryData = [];

    // Stock Transfer Modal
    public $showTransferModal = false;
    public $transferFromWarehouse = null;
    public $transferToWarehouse = '';
    public $transferProduct = '';
    public $transferQuantity = '';
    public $transferNotes = '';

    // Form fields
    public $name = '';
    public $code = '';
    public $address = '';
    public $city = '';
    public $manager_name = '';
    public $phone = '';
    public $type = 'main';
    public $is_active = true;

    // Search and filters
    public $search = '';
    public $typeFilter = '';
    public $statusFilter = '';

    // Inventory search
    public $inventorySearch = '';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:10|unique:warehouses,code',
        'address' => 'required|string|max:500',
        'city' => 'required|string|max:100',
        'manager_name' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'type' => 'required|in:main,retail,storage,overflow',
        'is_active' => 'boolean',
    ];

    protected array $transferRules = [
        'transferToWarehouse' => 'required|exists:warehouses,id|different:transferFromWarehouse.id',
        'transferProduct' => 'required|exists:products,id',
        'transferQuantity' => 'required|integer|min:1',
        'transferNotes' => 'nullable|string|max:500',
    ];

    public function render()
    {
        $warehouses = Warehouse::withCount(['inventory', 'sales', 'purchaseOrders'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%')
                ->orWhere('city', 'like', '%' . $this->search . '%'))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $typeOptions = [
            ['value' => '', 'label' => 'All Types'],
            ['value' => 'main', 'label' => 'Main Warehouse'],
            ['value' => 'retail', 'label' => 'Retail Store'],
            ['value' => 'storage', 'label' => 'Storage Facility'],
            ['value' => 'overflow', 'label' => 'Overflow Storage'],
        ];

        $statusOptions = [
            ['value' => '', 'label' => 'All Status'],
            ['value' => '1', 'label' => 'Active'],
            ['value' => '0', 'label' => 'Inactive'],
        ];

        return view('livewire.inventory.warehouse-management', [
            'warehouses' => $warehouses,
            'typeOptions' => $typeOptions,
            'statusOptions' => $statusOptions,
        ])->layout('layouts.app', ['title' => 'Warehouse Management']);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->selectedWarehouse = null;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function editWarehouse(Warehouse $warehouse)
    {
        $this->selectedWarehouse = $warehouse;
        $this->name = $warehouse->name;
        $this->code = $warehouse->code;
        $this->address = $warehouse->address;
        $this->city = $warehouse->city;
        $this->manager_name = $warehouse->manager_name ?? '';
        $this->phone = $warehouse->phone ?? '';
        $this->type = $warehouse->type;
        $this->is_active = $warehouse->is_active;
        $this->editMode = true;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function save()
    {
        if ($this->editMode) {
            $this->rules['code'] = 'required|string|max:10|unique:warehouses,code,' . $this->selectedWarehouse->id;
        }

        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'address' => $this->address,
                'city' => $this->city,
                'manager_name' => $this->manager_name,
                'phone' => $this->phone,
                'type' => $this->type,
                'is_active' => $this->is_active,
            ];

            if ($this->editMode) {
                $this->selectedWarehouse->update($data);
                $this->success('Warehouse updated successfully!');
            } else {
                Warehouse::create($data);
                $this->success('Warehouse created successfully!');
            }

            $this->showModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }

    public function deleteWarehouse(Warehouse $warehouse)
    {
        try {
            // Check if warehouse has inventory
            if ($warehouse->inventory()->sum('quantity_on_hand') > 0) {
                $this->error('Cannot delete warehouse with existing inventory. Please transfer or remove all stock first.');
                return;
            }

            // Check if warehouse has pending sales or purchase orders
            if (
                $warehouse->sales()->whereIn('status', ['pending', 'processing'])->exists() ||
                $warehouse->purchaseOrders()->whereIn('status', ['pending', 'ordered'])->exists()
            ) {
                $this->error('Cannot delete warehouse with pending orders. Please complete or cancel all pending orders first.');
                return;
            }

            $warehouse->delete();
            $this->success('Warehouse deleted successfully!');
        } catch (\Exception $e) {
            $this->error('An error occurred while deleting the warehouse.');
        }
    }

    // View Inventory Functions
    public function viewInventory(Warehouse $warehouse)
    {
        $this->inventoryWarehouse = $warehouse;
        $this->loadInventoryData();
        $this->showInventoryModal = true;
    }

    public function loadInventoryData()
    {
        if (!$this->inventoryWarehouse) return;

        $query = Inventory::with(['product.category', 'product.brand'])
            ->where('warehouse_id', $this->inventoryWarehouse->id)
            ->where('quantity_on_hand', '>', 0);

        if ($this->inventorySearch) {
            $query->whereHas('product', function ($q) {
                $q->where('name', 'like', '%' . $this->inventorySearch . '%')
                    ->orWhere('sku', 'like', '%' . $this->inventorySearch . '%');
            });
        }

        $this->inventoryData = $query->orderBy('quantity_on_hand', 'desc')->get();
    }

    public function updatedInventorySearch()
    {
        $this->loadInventoryData();
    }

    // Stock Transfer Functions
    public function openStockTransfer(Warehouse $fromWarehouse)
    {
        $this->transferFromWarehouse = $fromWarehouse;
        $this->resetTransferForm();
        $this->showTransferModal = true;
    }

    public function processStockTransfer()
    {
        $this->validate($this->transferRules);

        try {
            DB::beginTransaction();

            // Get source inventory
            $sourceInventory = Inventory::where('warehouse_id', $this->transferFromWarehouse->id)
                ->where('product_id', $this->transferProduct)
                ->first();

            if (!$sourceInventory || $sourceInventory->quantity_available < $this->transferQuantity) {
                $this->error('Insufficient stock available for transfer.');
                return;
            }

            // Create the main StockTransfer record
            $stockTransfer = StockTransfer::create([
                'from_warehouse_id' => $this->transferFromWarehouse->id,
                'to_warehouse_id' => $this->transferToWarehouse,
                'initiated_by' => auth()->id(),
                'status' => 'shipped', // Auto-complete for direct transfers
                'transfer_date' => now(),
                'shipped_at' => now(),
                'received_at' => now(), // Auto-receive for direct transfers
                'received_by' => auth()->id(),
                'notes' => $this->transferNotes,
            ]);

            // Create the transfer item
            $product = Product::find($this->transferProduct);
            $stockTransfer->items()->create([
                'product_id' => $this->transferProduct,
                'quantity_shipped' => $this->transferQuantity,
                'quantity_received' => $this->transferQuantity,
                'unit_cost' => $sourceInventory->average_cost ?? $product->cost_price,
                'notes' => 'Direct transfer',
            ]);

            // Get or create destination inventory
            $destinationInventory = Inventory::firstOrCreate(
                [
                    'warehouse_id' => $this->transferToWarehouse,
                    'product_id' => $this->transferProduct,
                ],
                [
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'quantity_available' => 0,
                ]
            );

            // Store old quantities for stock movements
            $sourceOldQuantity = $sourceInventory->quantity_on_hand;
            $destOldQuantity = $destinationInventory->quantity_on_hand;

            // Update quantities
            $sourceInventory->decrement('quantity_on_hand', $this->transferQuantity);
            $destinationInventory->increment('quantity_on_hand', $this->transferQuantity);

            // Update available quantities
            $sourceInventory->update(['quantity_available' => $sourceInventory->quantity_on_hand - $sourceInventory->quantity_reserved]);
            $destinationInventory->update(['quantity_available' => $destinationInventory->quantity_on_hand - $destinationInventory->quantity_reserved]);

            // Create stock movements with proper reference to the StockTransfer
            $unitCost = $sourceInventory->average_cost ?? $product->cost_price;

            // Transfer out movement
            StockMovement::create([
                'product_id' => $this->transferProduct,
                'warehouse_id' => $this->transferFromWarehouse->id,
                'type' => 'transfer',
                'quantity_before' => $sourceOldQuantity,
                'quantity_changed' => -$this->transferQuantity,
                'quantity_after' => $sourceInventory->quantity_on_hand,
                'unit_cost' => $unitCost,
                'reference_type' => StockTransfer::class,
                'reference_id' => $stockTransfer->id,
                'user_id' => auth()->id(),
                'notes' => 'Transfer OUT to ' . Warehouse::find($this->transferToWarehouse)->name . ' (Transfer #' . $stockTransfer->transfer_number . ')',
            ]);

            // Transfer in movement
            StockMovement::create([
                'product_id' => $this->transferProduct,
                'warehouse_id' => $this->transferToWarehouse,
                'type' => 'transfer',
                'quantity_before' => $destOldQuantity,
                'quantity_changed' => $this->transferQuantity,
                'quantity_after' => $destinationInventory->quantity_on_hand,
                'unit_cost' => $unitCost,
                'reference_type' => StockTransfer::class,
                'reference_id' => $stockTransfer->id,
                'user_id' => auth()->id(),
                'notes' => 'Transfer IN from ' . $this->transferFromWarehouse->name . ' (Transfer #' . $stockTransfer->transfer_number . ')',
            ]);

            DB::commit();

            $this->success('Stock transfer completed successfully! Transfer #' . $stockTransfer->transfer_number);
            $this->showTransferModal = false;
            $this->resetTransferForm();
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('An error occurred during stock transfer: ' . $e->getMessage());
        }
    }

    public function getAvailableProducts()
    {
        if (!$this->transferFromWarehouse) return collect();

        return Inventory::with('product')
            ->where('warehouse_id', $this->transferFromWarehouse->id)
            ->where('quantity_available', '>', 0)
            ->get()
            ->map(fn($inventory) => [
                'value' => $inventory->product_id,
                'label' => $inventory->product->name . ' (Available: ' . $inventory->quantity_available . ')'
            ]);
    }

    public function getDestinationWarehouses()
    {
        return Warehouse::where('is_active', true)
            ->where('id', '!=', $this->transferFromWarehouse?->id)
            ->get()
            ->map(fn($warehouse) => [
                'value' => $warehouse->id,
                'label' => $warehouse->name . ' (' . $warehouse->code . ')'
            ]);
    }

    public function getMaxTransferQuantity()
    {
        if (!$this->transferFromWarehouse || !$this->transferProduct) return 0;

        $inventory = Inventory::where('warehouse_id', $this->transferFromWarehouse->id)
            ->where('product_id', $this->transferProduct)
            ->first();

        return $inventory ? $inventory->quantity_available : 0;
    }

    public function resetForm()
    {
        $this->reset([
            'name',
            'code',
            'address',
            'city',
            'manager_name',
            'phone',
            'type',
            'is_active'
        ]);
    }

    public function resetTransferForm()
    {
        $this->reset([
            'transferToWarehouse',
            'transferProduct',
            'transferQuantity',
            'transferNotes'
        ]);
    }

    public function closeInventoryModal()
    {
        $this->showInventoryModal = false;
        $this->inventoryWarehouse = null;
        $this->inventoryData = [];
        $this->inventorySearch = '';
    }

    public function closeTransferModal()
    {
        $this->showTransferModal = false;
        $this->transferFromWarehouse = null;
        $this->resetTransferForm();
    }
}
