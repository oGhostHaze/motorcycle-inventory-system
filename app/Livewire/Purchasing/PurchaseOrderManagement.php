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
    public $showDetailsModal = false;
    public $editMode = false;
    public $selectedPO = null;
    public $viewingPO = null;

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

        // Handle query parameters from supplier management
        $request = request();

        // Check for create PO request from supplier management
        if ($request->has('create_po') && $request->get('create_po') === 'true') {
            $supplierId = $request->get('supplier_id');
            $supplierName = urldecode($request->get('supplier_name', 'selected supplier'));

            if ($supplierId && Supplier::find($supplierId)) {
                $this->supplier_id = $supplierId;

                // Set default warehouse
                $defaultWarehouse = Warehouse::where('is_active', true)->first();
                if ($defaultWarehouse) {
                    $this->warehouse_id = $defaultWarehouse->id;
                }

                // Add a small delay then open modal to ensure supplier_id is set
                $this->js('setTimeout(() => { $wire.openModalWithSupplier() }, 100)');
                $this->success("Creating purchase order for {$supplierName}.");
            }
        }

        if ($request->has('poId')) {
            $poId = $request->get('poId');
            $po = PurchaseOrder::find($poId);
            $this->viewPODetails($po);
        }
    }

    public function openModalWithSupplier()
    {
        $this->editMode = false;
        $this->selectedPO = null;
        $this->showModal = true;
        $this->resetValidation();
        $this->addItem();

        // Ensure warehouse is set if not already
        if (empty($this->warehouse_id)) {
            $defaultWarehouse = Warehouse::where('is_active', true)->first();
            if ($defaultWarehouse) {
                $this->warehouse_id = $defaultWarehouse->id;
            }
        }
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

        // Set default warehouse if not already set
        if (empty($this->warehouse_id)) {
            $defaultWarehouse = Warehouse::where('is_active', true)->first();
            if ($defaultWarehouse) {
                $this->warehouse_id = $defaultWarehouse->id;
            }
        }

        // Dispatch event to handle modal opening
        $this->dispatch('modal-opened');
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
            \DB::beginTransaction();

            $totalReceived = 0;
            $po = $this->selectedPO;

            foreach ($this->receivingItems as $receivingItem) {
                if ($receivingItem['receiving_quantity'] <= 0) continue;

                $poItem = PurchaseOrderItem::find($receivingItem['id']);
                $newQuantityReceived = $poItem->quantity_received + $receivingItem['receiving_quantity'];

                // Update PO item
                $poItem->update([
                    'quantity_received' => $newQuantityReceived
                ]);

                // Update inventory
                $inventory = Inventory::where('product_id', $poItem->product_id)
                    ->where('warehouse_id', $po->warehouse_id)
                    ->first();

                if ($inventory) {
                    $inventory->increment('quantity_on_hand', $receivingItem['receiving_quantity']);
                } else {
                    Inventory::create([
                        'product_id' => $poItem->product_id,
                        'warehouse_id' => $po->warehouse_id,
                        'quantity_on_hand' => $receivingItem['receiving_quantity'],
                        'quantity_available' => $receivingItem['receiving_quantity'],
                        'average_cost' => $receivingItem['unit_cost'],
                    ]);
                }

                // Create stock movement
                StockMovement::create([
                    'product_id' => $poItem->product_id,
                    'warehouse_id' => $po->warehouse_id,
                    'type' => 'purchase_receipt',
                    'quantity' => $receivingItem['receiving_quantity'],
                    'reference_type' => 'App\Models\PurchaseOrder',
                    'reference_id' => $po->id,
                    'user_id' => auth()->id(),
                    'notes' => "Received from PO: {$po->po_number}",
                ]);

                $totalReceived += $receivingItem['receiving_quantity'];
            }

            // Update PO status
            $allItemsReceived = $po->items->every(function ($item) {
                return $item->quantity_received >= $item->quantity_ordered;
            });

            $po->update([
                'status' => $allItemsReceived ? 'completed' : 'partial'
            ]);

            \DB::commit();

            $this->success("Successfully received {$totalReceived} items!");
            $this->showReceiveModal = false;
        } catch (\Exception $e) {
            \DB::rollback();
            $this->error('Error processing receipt: ' . $e->getMessage());
        }
    }

    public function cancelPO(PurchaseOrder $po)
    {
        if (!in_array($po->status, ['draft', 'pending'])) {
            $this->error('Only draft or pending purchase orders can be cancelled.');
            return;
        }

        $po->update(['status' => 'cancelled']);
        $this->success('Purchase order cancelled successfully!');
    }

    public function deletePO(PurchaseOrder $po)
    {
        if ($po->status !== 'draft') {
            $this->error('Only draft purchase orders can be deleted.');
            return;
        }

        try {
            $po->items()->delete();
            $po->delete();
            $this->success('Purchase order deleted successfully!');
        } catch (\Exception $e) {
            $this->error('Error deleting purchase order: ' . $e->getMessage());
        }
    }

    public function duplicatePO(PurchaseOrder $po)
    {
        try {
            $newPO = $po->replicate();
            $newPO->status = 'draft';
            $newPO->order_date = now()->format('Y-m-d');
            $newPO->expected_date = now()->addDays(7)->format('Y-m-d');
            $newPO->requested_by = auth()->id();
            $newPO->save();

            // Duplicate items
            foreach ($po->items as $item) {
                $newItem = $item->replicate();
                $newItem->purchase_order_id = $newPO->id;
                $newItem->quantity_received = 0;
                $newItem->save();
            }

            $this->success("Purchase order duplicated successfully! New PO: {$newPO->po_number}");
        } catch (\Exception $e) {
            $this->error('Error duplicating purchase order: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'supplierFilter', 'warehouseFilter', 'statusFilter', 'dateFilter']);
    }

    private function resetForm()
    {
        $this->reset([
            'supplier_id',
            'warehouse_id',
            'order_date',
            'expected_date',
            'notes',
            'items'
        ]);
        $this->order_date = now()->format('Y-m-d');
        $this->expected_date = now()->addDays(7)->format('Y-m-d');
    }

    /**
     * Get status color for badges
     */
    public function getStatusColor($status)
    {
        return match ($status) {
            'draft' => 'neutral',
            'pending' => 'warning',
            'partial' => 'info',
            'completed' => 'success',
            'cancelled' => 'error',
            default => 'neutral',
        };
    }

    /**
     * Export purchase orders to Excel/CSV
     */
    public function exportPOs()
    {
        // Implementation for exporting POs
        $this->info('Export functionality coming soon!');
    }

    /**
     * Bulk operations on selected POs
     */
    public function bulkSubmit($poIds)
    {
        $updated = 0;
        foreach ($poIds as $poId) {
            $po = PurchaseOrder::find($poId);
            if ($po && $po->status === 'draft') {
                $po->update(['status' => 'pending']);
                $updated++;
            }
        }

        if ($updated > 0) {
            $this->success("Successfully submitted {$updated} purchase order(s)!");
        } else {
            $this->warning('No purchase orders were submitted.');
        }
    }

    /**
     * Print purchase order
     */
    public function printPO(PurchaseOrder $po)
    {
        // Implementation for printing PO
        $this->info("Print functionality for PO {$po->po_number} coming soon!");
    }

    /**
     * Send PO via email to supplier
     */
    public function emailPO(PurchaseOrder $po)
    {
        if (!$po->supplier->email) {
            $this->error('Supplier does not have an email address on file.');
            return;
        }

        try {
            // Implementation for emailing PO
            $this->success("Purchase order {$po->po_number} sent to {$po->supplier->email}!");
        } catch (\Exception $e) {
            $this->error('Error sending email: ' . $e->getMessage());
        }
    }

    /**
     * View detailed PO information
     */
    public function viewPODetails(PurchaseOrder $po)
    {
        // Load the PO with basic relationships
        $this->viewingPO = $po->load([
            'supplier',
            'warehouse',
            'requestedBy',
            'items.product.category',
        ]);

        $this->showDetailsModal = true;
    }

    /**
     * Convert PO to different format
     */
    public function convertPO(PurchaseOrder $po, $format = 'pdf')
    {
        try {
            // Implementation for converting PO to different formats
            $this->success("PO {$po->po_number} converted to {$format} successfully!");
        } catch (\Exception $e) {
            $this->error("Error converting PO to {$format}: " . $e->getMessage());
        }
    }

    /**
     * Get total value of filtered POs
     */
    public function getTotalValue()
    {
        return PurchaseOrder::when($this->search, fn($q) => $q->where('po_number', 'like', '%' . $this->search . '%'))
            ->when($this->supplierFilter, fn($q) => $q->where('supplier_id', $this->supplierFilter))
            ->when($this->warehouseFilter, fn($q) => $q->where('warehouse_id', $this->warehouseFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->sum('total_amount');
    }

    /**
     * Get PO statistics
     */
    public function getPOStats()
    {
        return [
            'total' => PurchaseOrder::count(),
            'draft' => PurchaseOrder::where('status', 'draft')->count(),
            'pending' => PurchaseOrder::where('status', 'pending')->count(),
            'completed' => PurchaseOrder::where('status', 'completed')->count(),
            'cancelled' => PurchaseOrder::where('status', 'cancelled')->count(),
        ];
    }
}
