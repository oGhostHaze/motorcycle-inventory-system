<?php

namespace App\Livewire\Purchasing;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class PurchaseOrderManagement extends Component
{
    use WithPagination;
    use Toast;

    public $showModal = false;
    public $showReceiveModal = false;
    public $editMode = false;
    public $selectedPO = null;

    // Form fields
    public $supplier_id = '';
    public $warehouse_id = '';
    public $order_date = '';
    public $expected_date = '';
    public $notes = '';
    public $items = [];

    // Receiving fields
    public $receivingItems = [];

    // Search and filters
    public $search = '';
    public $supplierFilter = '';
    public $warehouseFilter = '';
    public $statusFilter = '';
    public $dateFilter = '';

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'warehouse_id' => 'required|exists:warehouses,id',
        'order_date' => 'required|date',
        'expected_date' => 'required|date|after_or_equal:order_date',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_cost' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->order_date = now()->format('Y-m-d');
        $this->expected_date = now()->addDays(7)->format('Y-m-d');
    }

    public function render()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'warehouse', 'requestedBy', 'items'])
            ->when($this->search, fn($q) => $q->where('po_number', 'like', '%' . $this->search . '%')
                ->orWhereHas('supplier', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                }))
            ->when($this->supplierFilter, fn($q) => $q->where('supplier_id', $this->supplierFilter))
            ->when($this->warehouseFilter, fn($q) => $q->where('warehouse_id', $this->warehouseFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFilter, function ($q) {
                switch ($this->dateFilter) {
                    case 'today':
                        return $q->whereDate('order_date', today());
                    case 'week':
                        return $q->whereBetween('order_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    case 'month':
                        return $q->whereMonth('order_date', now()->month);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get();

        $filterOptions = [
            'suppliers' => $suppliers->map(fn($s) => ['value' => $s->id, 'label' => $s->name]),
            'warehouses' => $warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name]),
            'statuses' => [
                ['value' => '', 'label' => 'All Status'],
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'partial', 'label' => 'Partially Received'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
            'dates' => [
                ['value' => '', 'label' => 'All Dates'],
                ['value' => 'today', 'label' => 'Today'],
                ['value' => 'week', 'label' => 'This Week'],
                ['value' => 'month', 'label' => 'This Month'],
            ]
        ];

        return view('livewire.purchasing.purchase-order-management', [
            'purchaseOrders' => $purchaseOrders,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'products' => $products,
            'filterOptions' => $filterOptions,
        ])->layout('layouts.app', ['title' => 'Purchase Orders']);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->selectedPO = null;
        $this->showModal = true;
        $this->resetValidation();
        $this->addItem();
    }

    public function editPO(PurchaseOrder $po)
    {
        if ($po->status !== 'draft') {
            $this->error('Only draft purchase orders can be edited.');
            return;
        }

        $this->selectedPO = $po;
        $this->supplier_id = $po->supplier_id;
        $this->warehouse_id = $po->warehouse_id;
        $this->order_date = $po->order_date->format('Y-m-d');
        $this->expected_date = $po->expected_date->format('Y-m-d');
        $this->notes = $po->notes ?? '';

        $this->items = $po->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity_ordered,
                'unit_cost' => $item->unit_cost,
            ];
        })->toArray();

        $this->editMode = true;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => 1,
            'unit_cost' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $this->validate();

        try {
            $totalAmount = collect($this->items)->sum(fn($item) => $item['quantity'] * $item['unit_cost']);

            $data = [
                'supplier_id' => $this->supplier_id,
                'warehouse_id' => $this->warehouse_id,
                'requested_by' => auth()->id(),
                'status' => 'draft',
                'total_amount' => $totalAmount,
                'order_date' => $this->order_date,
                'expected_date' => $this->expected_date,
                'notes' => $this->notes,
            ];

            if ($this->editMode) {
                $this->selectedPO->update($data);
                // Delete existing items and recreate
                $this->selectedPO->items()->delete();
                $po = $this->selectedPO;
            } else {
                $po = PurchaseOrder::create($data);
            }

            // Create items
            foreach ($this->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            $this->success($this->editMode ? 'Purchase order updated successfully!' : 'Purchase order created successfully!');
            $this->showModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->error('Error saving purchase order: ' . $e->getMessage());
        }
    }

    public function submitPO(PurchaseOrder $po)
    {
        if ($po->status !== 'draft') {
            $this->error('Only draft purchase orders can be submitted.');
            return;
        }

        $po->update(['status' => 'pending']);
        $this->success('Purchase order submitted successfully!');
    }

    public function openReceiveModal(PurchaseOrder $po)
    {
        if (!in_array($po->status, ['pending', 'partial'])) {
            $this->error('This purchase order cannot be received.');
            return;
        }

        $this->selectedPO = $po;
        $this->receivingItems = $po->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_name' => $item->product->name,
                'quantity_ordered' => $item->quantity_ordered,
                'quantity_received' => $item->quantity_received,
                'quantity_pending' => $item->quantity_pending,
                'receiving_quantity' => $item->quantity_pending,
                'unit_cost' => $item->unit_cost,
            ];
        })->toArray();

        $this->showReceiveModal = true;
    }

    public function processReceiving()
    {
        $this->validate([
            'receivingItems.*.receiving_quantity' => 'required|integer|min:0',
        ]);

        try {
            $totalReceived = 0;
            $totalOrdered = 0;

            foreach ($this->receivingItems as $item) {
                $poItem = PurchaseOrderItem::find($item['id']);
                $receivingQty = $item['receiving_quantity'];

                if ($receivingQty > 0) {
                    // Update PO item
                    $newReceived = $poItem->quantity_received + $receivingQty;
                    $poItem->update(['quantity_received' => $newReceived]);

                    // Update inventory
                    $inventory = Inventory::firstOrCreate([
                        'product_id' => $poItem->product_id,
                        'warehouse_id' => $this->selectedPO->warehouse_id,
                    ], [
                        'quantity_on_hand' => 0,
                        'quantity_reserved' => 0,
                        'average_cost' => $poItem->unit_cost,
                    ]);

                    $oldQuantity = $inventory->quantity_on_hand;
                    $newQuantity = $oldQuantity + $receivingQty;

                    // Update average cost
                    $newAvgCost = (($oldQuantity * $inventory->average_cost) + ($receivingQty * $poItem->unit_cost)) / $newQuantity;

                    $inventory->update([
                        'quantity_on_hand' => $newQuantity,
                        'average_cost' => $newAvgCost,
                    ]);

                    // Create stock movement
                    StockMovement::create([
                        'product_id' => $poItem->product_id,
                        'warehouse_id' => $this->selectedPO->warehouse_id,
                        'type' => 'purchase',
                        'quantity_before' => $oldQuantity,
                        'quantity_changed' => $receivingQty,
                        'quantity_after' => $newQuantity,
                        'unit_cost' => $poItem->unit_cost,
                        'reference_id' => $this->selectedPO->id,
                        'reference_type' => PurchaseOrder::class,
                        'user_id' => auth()->id(),
                        'notes' => 'Received from PO: ' . $this->selectedPO->po_number,
                    ]);
                }

                $totalReceived += $poItem->quantity_received;
                $totalOrdered += $poItem->quantity_ordered;
            }

            // Update PO status
            if ($totalReceived >= $totalOrdered) {
                $this->selectedPO->update([
                    'status' => 'completed',
                    'received_date' => now(),
                ]);
            } elseif ($totalReceived > 0) {
                $this->selectedPO->update(['status' => 'partial']);
            }

            $this->success('Items received successfully!');
            $this->showReceiveModal = false;
        } catch (\Exception $e) {
            $this->error('Error processing receiving: ' . $e->getMessage());
        }
    }

    public function cancelPO(PurchaseOrder $po)
    {
        if (!in_array($po->status, ['draft', 'pending'])) {
            $this->error('This purchase order cannot be cancelled.');
            return;
        }

        $po->update(['status' => 'cancelled']);
        $this->success('Purchase order cancelled successfully!');
    }

    public function clearFilters()
    {
        $this->reset(['search', 'supplierFilter', 'warehouseFilter', 'statusFilter', 'dateFilter']);
    }

    private function resetForm()
    {
        $this->reset(['supplier_id', 'warehouse_id', 'order_date', 'expected_date', 'notes', 'items']);
        $this->order_date = now()->format('Y-m-d');
        $this->expected_date = now()->addDays(7)->format('Y-m-d');
    }
}
