<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

class ReturnsManagement extends Component
{
    use WithPagination;
    use Toast;

    // Modal states
    public $showReturnModal = false;
    public $showExchangeModal = false;
    public $showDetailsModal = false;
    public $showProcessModal = false;

    // Selected records
    public $selectedSale = null;
    public $selectedReturn = null;

    // Return form
    public $returnType = 'refund'; // refund, exchange, store_credit
    public $returnReason = 'defective';
    public $returnNotes = '';
    public $refundAmount = 0;
    public $restockCondition = 'good'; // good, damaged, defective
    public $selectedItems = [];
    public $returnItems = [];

    // Exchange form
    public $exchangeItems = [];
    public $selectedExchangeProducts = [];
    public $exchangeTotal = 0;
    public $additionalPayment = 0;

    // Search and filters
    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $reasonFilter = '';
    public $dateFilter = '';
    public $startDate = '';
    public $endDate = '';

    protected $rules = [
        'returnType' => 'required|in:refund,exchange,store_credit',
        'returnReason' => 'required|string|max:255',
        'returnNotes' => 'nullable|string|max:1000',
        'restockCondition' => 'required|in:good,damaged,defective',
        'returnItems' => 'required|array|min:1',
        'returnItems.*.quantity' => 'required|integer|min:1',
        'returnItems.*.reason' => 'required|string',
    ];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $returns = SaleReturn::with(['sale.customer', 'warehouse', 'user', 'items.product'])
            ->when($this->getFiltersQuery(), fn($q) => $this->applyFilters($q))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $filterOptions = [
            'statuses' => [
                ['id' => '', 'name' => 'All Status'],
                ['id' => 'pending', 'name' => 'Pending'],
                ['id' => 'approved', 'name' => 'Approved'],
                ['id' => 'processed', 'name' => 'Processed'],
                ['id' => 'rejected', 'name' => 'Rejected'],
            ],
            'types' => [
                ['id' => '', 'name' => 'All Types'],
                ['id' => 'refund', 'name' => 'Refund'],
                ['id' => 'exchange', 'name' => 'Exchange'],
                ['id' => 'store_credit', 'name' => 'Store Credit'],
            ],
            'reasons' => [
                ['id' => '', 'name' => 'All Reasons'],
                ['id' => 'defective', 'name' => 'Defective Product'],
                ['id' => 'wrong_item', 'name' => 'Wrong Item'],
                ['id' => 'not_as_described', 'name' => 'Not as Described'],
                ['id' => 'customer_changed_mind', 'name' => 'Customer Changed Mind'],
                ['id' => 'damaged_shipping', 'name' => 'Damaged in Shipping'],
                ['id' => 'warranty_claim', 'name' => 'Warranty Claim'],
                ['id' => 'other', 'name' => 'Other'],
            ],
            'dates' => [
                ['id' => '', 'name' => 'All Time'],
                ['id' => 'today', 'name' => 'Today'],
                ['id' => 'week', 'name' => 'This Week'],
                ['id' => 'month', 'name' => 'This Month'],
                ['id' => 'custom', 'name' => 'Custom Range'],
            ]
        ];

        // Calculate stats
        $totalReturns = $returns->total();
        $pendingReturns = SaleReturn::where('status', 'pending')->count();
        $totalRefundAmount = SaleReturn::where('status', 'processed')
            ->where('type', 'refund')
            ->sum('refund_amount');

        return view('livewire.sales.returns-management', [
            'returns' => $returns,
            'filterOptions' => $filterOptions,
            'totalReturns' => $totalReturns,
            'pendingReturns' => $pendingReturns,
            'totalRefundAmount' => $totalRefundAmount,
        ])->layout('layouts.app', ['title' => 'Returns & Exchanges']);
    }

    public function openReturnModal($saleId = null)
    {
        $this->resetReturnForm();

        if ($saleId) {
            $this->selectedSale = Sale::with(['items.product', 'customer', 'warehouse'])
                ->find($saleId);

            if (!$this->selectedSale) {
                $this->error('Sale not found.');
                return;
            }

            if ($this->selectedSale->status !== 'completed') {
                $this->error('Only completed sales can be returned.');
                return;
            }

            // Prepare return items from sale items
            $this->prepareReturnItems();
        }

        $this->showReturnModal = true;
    }

    public function searchSaleForReturn()
    {
        if (empty($this->search)) {
            $this->selectedSale = null;
            return;
        }

        $sale = Sale::where('invoice_number', $this->search)
            ->where('status', 'completed')
            ->with(['items.product', 'customer', 'warehouse'])
            ->first();

        if ($sale) {
            $this->selectedSale = $sale;
            $this->prepareReturnItems();
            $this->success('Sale found! Select items to return.');
        } else {
            $this->selectedSale = null;
            $this->error('No completed sale found with this invoice number.');
        }
    }

    private function prepareReturnItems()
    {
        $this->returnItems = [];

        // Get already returned quantities for this sale
        $returnedQuantities = $this->getReturnedQuantities($this->selectedSale->id);

        foreach ($this->selectedSale->items as $item) {
            $alreadyReturned = $returnedQuantities[$item->id] ?? 0;
            $availableToReturn = $item->quantity - $alreadyReturned;

            // Only show items that can still be returned
            if ($availableToReturn > 0) {
                $this->returnItems[] = [
                    'sale_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'sku' => $item->product->sku,
                    'original_quantity' => $item->quantity,
                    'already_returned' => $alreadyReturned,
                    'available_to_return' => $availableToReturn,
                    'unit_price' => $item->unit_price,
                    'quantity' => 0,
                    'reason' => '',
                    'selected' => false,
                ];
            }
        }

        if (empty($this->returnItems)) {
            $this->error('All items from this sale have already been returned.');
        }
    }

    public function toggleReturnItem($index)
    {
        $this->returnItems[$index]['selected'] = !$this->returnItems[$index]['selected'];

        if (!$this->returnItems[$index]['selected']) {
            $this->returnItems[$index]['quantity'] = 0;
        } else {
            $this->returnItems[$index]['quantity'] = 1;
        }

        $this->calculateRefundAmount();
    }

    public function updateReturnQuantity($index, $quantity)
    {
        $maxQuantity = $this->returnItems[$index]['available_to_return'];

        if ($quantity > $maxQuantity) {
            $this->returnItems[$index]['quantity'] = $maxQuantity;
            $this->error("Maximum available quantity for {$this->returnItems[$index]['product_name']} is {$maxQuantity}");
        } else {
            $this->returnItems[$index]['quantity'] = max(0, $quantity);
        }

        $this->calculateRefundAmount();
    }

    private function calculateRefundAmount()
    {
        $total = 0;
        foreach ($this->returnItems as $item) {
            if ($item['selected'] && $item['quantity'] > 0) {
                $total += $item['quantity'] * $item['unit_price'];
            }
        }
        $this->refundAmount = $total;
    }

    public function approveReturn(SaleReturn $return)
    {
        if ($return->status !== 'pending') {
            $this->error('Only pending returns can be approved.');
            return;
        }

        try {
            $return->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $this->success('Return approved successfully!');
        } catch (\Exception $e) {
            $this->error('Error approving return: ' . $e->getMessage());
        }
    }

    private function restoreInventory(SaleReturnItem $returnItem, $warehouseId)
    {
        $inventory = Inventory::where('product_id', $returnItem->product_id)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($inventory) {
            $inventory->increment('quantity_on_hand', $returnItem->quantity);
        }
    }

    private function createStockMovement(SaleReturnItem $returnItem, SaleReturn $return)
    {
        $inventory = Inventory::where('product_id', $returnItem->product_id)
            ->where('warehouse_id', $return->warehouse_id)
            ->first();

        if ($inventory) {
            StockMovement::create([
                'product_id' => $returnItem->product_id,
                'warehouse_id' => $return->warehouse_id,
                'type' => 'return',
                'quantity_before' => $inventory->quantity_on_hand - $returnItem->quantity,
                'quantity_changed' => $returnItem->quantity,
                'quantity_after' => $inventory->quantity_on_hand,
                'unit_cost' => $inventory->average_cost,
                'reference_id' => $return->id,
                'reference_type' => SaleReturn::class,
                'user_id' => auth()->id(),
                'notes' => "Return: {$return->return_number} - {$return->reason}",
            ]);
        }
    }

    private function processRefund(SaleReturn $return)
    {
        // This would integrate with your payment processing system
        // For now, we'll just log it
        \Log::info("Refund processed for return {$return->return_number}: ₱{$return->refund_amount}");
    }

    private function addStoreCredit(SaleReturn $return)
    {
        // Add store credit to customer account
        if ($return->customer) {
            $return->customer->increment('store_credit', $return->refund_amount);
        }
    }

    public function viewReturnDetails(SaleReturn $return)
    {
        $this->selectedReturn = $return->load(['sale.customer', 'warehouse', 'user', 'items.product']);
        $this->showDetailsModal = true;
    }

    private function resetReturnForm()
    {
        $this->reset([
            'selectedSale',
            'returnType',
            'returnReason',
            'returnNotes',
            'refundAmount',
            'restockCondition',
            'returnItems'
        ]);
        $this->returnType = 'refund';
        $this->restockCondition = 'good';
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'typeFilter', 'reasonFilter', 'dateFilter']);
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    private function getFiltersQuery()
    {
        return $this->search || $this->statusFilter || $this->typeFilter ||
            $this->reasonFilter || $this->dateFilter;
    }

    private function applyFilters($query)
    {
        return $query->when($this->search, fn($q) => $q->where('return_number', 'like', '%' . $this->search . '%')
            ->orWhereHas('sale', function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%');
            })
            ->orWhereHas('sale.customer', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            }))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->reasonFilter, fn($q) => $q->where('reason', $this->reasonFilter))
            ->when($this->dateFilter, function ($query) {
                switch ($this->dateFilter) {
                    case 'today':
                        return $query->whereDate('created_at', now());
                    case 'week':
                        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    case 'month':
                        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    case 'custom':
                        return $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
                }
            });
    }

    public function processReturn()
    {
        $this->validate();

        $selectedItems = collect($this->returnItems)
            ->filter(fn($item) => $item['selected'] && $item['quantity'] > 0)
            ->values()
            ->toArray();

        if (empty($selectedItems)) {
            $this->error('Please select at least one item to return.');
            return;
        }

        // Check if there's an active shift for returns processing
        $activeShift = \App\Models\SalesShift::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (!$activeShift) {
            $this->error('No active sales shift found. Please start a shift before processing returns.');
            return;
        }

        try {
            \DB::transaction(function () use ($selectedItems, $activeShift) {
                // Generate return number
                $returnNumber = 'RET-' . now()->format('Ymd') . '-' . str_pad(SalesReturn::whereDate('created_at', now())->count() + 1, 4, '0', STR_PAD_LEFT);

                // Create return record
                $return = SalesReturn::create([
                    'return_number' => $returnNumber,
                    'sale_id' => $this->selectedSale->id,
                    'customer_id' => $this->selectedSale->customer_id,
                    'warehouse_id' => $this->selectedSale->warehouse_id,
                    'user_id' => auth()->id(),
                    'sales_shift_id' => $activeShift->id, // Link to active shift
                    'type' => $this->returnType,
                    'reason' => $this->returnReason,
                    'notes' => $this->returnNotes,
                    'refund_amount' => $this->refundAmount,
                    'restock_condition' => $this->restockCondition,
                    'status' => 'pending',
                ]);

                // Create return items
                foreach ($selectedItems as $item) {
                    SaleReturnItem::create([
                        'sale_return_id' => $return->id,
                        'sale_item_id' => $item['sale_item_id'],
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'reason' => $item['reason'],
                        'condition' => $this->restockCondition,
                    ]);
                }

                // Update shift totals
                $this->updateShiftTotals($activeShift, $return);
            });

            $this->success("Return {$returnNumber} created successfully and linked to current shift!");
            $this->showReturnModal = false;
            $this->resetReturnForm();
        } catch (\Exception $e) {
            $this->error('Error creating return: ' . $e->getMessage());
        }
    }

    private function updateShiftTotals($shift, $return)
    {
        // Update shift return totals
        $shift->increment('total_returns_count', 1);
        $shift->increment('total_returns_amount', $return->refund_amount);

        // If this is a cash refund, reduce cash total
        if ($return->type === 'refund' && $return->sale->payment_method === 'cash') {
            $shift->decrement('cash_sales', $return->refund_amount);
        }

        // Update overall shift totals
        $shift->decrement('total_sales', $return->refund_amount);
    }

    public function processApprovedReturn(SaleReturn $return)
    {
        if ($return->status !== 'approved') {
            $this->error('Return must be approved before processing.');
            return;
        }

        try {
            \DB::transaction(function () use ($return) {
                // Update return status
                $return->update([
                    'status' => 'processed',
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);

                // Process each return item
                foreach ($return->items as $returnItem) {
                    // Update the sale item's returned quantity
                    $saleItem = \App\Models\SaleItem::find($returnItem->sale_item_id);
                    if ($saleItem) {
                        $saleItem->incrementReturnedQuantity($returnItem->quantity);
                    }

                    // Restore inventory only if condition is good
                    if ($return->restock_condition === 'good') {
                        $this->restoreInventory($returnItem, $return->warehouse_id);
                    }

                    // Create stock movement record
                    $this->createStockMovement($returnItem, $return);
                }

                // Update shift processing status
                if ($return->salesShift) {
                    $return->salesShift->increment('processed_returns_count', 1);
                    $return->salesShift->increment('processed_returns_amount', $return->refund_amount);
                }

                // Handle refund/store credit
                if ($return->type === 'refund') {
                    $this->processRefund($return);
                } elseif ($return->type === 'store_credit') {
                    $this->addStoreCredit($return);
                }
            });

            $this->success('Return processed successfully! Sale items and shift totals updated.');
        } catch (\Exception $e) {
            $this->error('Error processing return: ' . $e->getMessage());
        }
    }

    /**
     * Enhanced method to get returned quantities using the sale_items table
     */
    private function getReturnedQuantities($saleId)
    {
        // Get returned quantities from sale_items.returned_quantity column
        return \App\Models\SaleItem::where('sale_id', $saleId)
            ->pluck('returned_quantity', 'id')
            ->toArray();
    }

    /**
     * Reject a return and restore any temporarily allocated quantities
     */
    public function rejectReturn(SaleReturn $return)
    {
        if ($return->status !== 'pending') {
            $this->error('Only pending returns can be rejected.');
            return;
        }

        try {
            $return->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
            ]);

            $this->success('Return rejected.');
        } catch (\Exception $e) {
            $this->error('Error rejecting return: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a processed return (for administrative purposes)
     */
    public function cancelProcessedReturn(SaleReturn $return)
    {
        if ($return->status !== 'processed') {
            $this->error('Only processed returns can be cancelled.');
            return;
        }

        try {
            \DB::transaction(function () use ($return) {
                // Reverse the sale item returned quantities
                foreach ($return->items as $returnItem) {
                    $saleItem = \App\Models\SaleItem::find($returnItem->sale_item_id);
                    if ($saleItem) {
                        $saleItem->decrementReturnedQuantity($returnItem->quantity);
                    }

                    // Remove inventory if it was restored
                    if ($return->restock_condition === 'good') {
                        $this->removeRestoredInventory($returnItem, $return->warehouse_id);
                    }

                    // Create reversal stock movement
                    $this->createReversalStockMovement($returnItem, $return);
                }

                // Update return status
                $return->update([
                    'status' => 'cancelled',
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => now(),
                ]);

                // Reverse refund/store credit
                if ($return->type === 'store_credit' && $return->customer) {
                    $return->customer->decrement('store_credit', $return->refund_amount);
                }
            });

            $this->success('Return cancelled successfully. All changes have been reversed.');
        } catch (\Exception $e) {
            $this->error('Error cancelling return: ' . $e->getMessage());
        }
    }

    private function removeRestoredInventory(SaleReturnItem $returnItem, $warehouseId)
    {
        $inventory = Inventory::where('product_id', $returnItem->product_id)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($inventory) {
            $inventory->decrement('quantity_on_hand', $returnItem->quantity);
        }
    }

    private function createReversalStockMovement(SaleReturnItem $returnItem, SaleReturn $return)
    {
        $inventory = Inventory::where('product_id', $returnItem->product_id)
            ->where('warehouse_id', $return->warehouse_id)
            ->first();

        if ($inventory) {
            StockMovement::create([
                'product_id' => $returnItem->product_id,
                'warehouse_id' => $return->warehouse_id,
                'type' => 'adjustment', // Fixed: Using valid enum value
                'quantity_before' => $inventory->quantity_on_hand + $returnItem->quantity,
                'quantity_changed' => -$returnItem->quantity,
                'quantity_after' => $inventory->quantity_on_hand,
                'unit_cost' => $inventory->average_cost,
                'reference_id' => $return->id,
                'reference_type' => SaleReturn::class,
                'user_id' => auth()->id(),
                'notes' => "Return cancellation: {$return->return_number} - Reversed inventory adjustment",
            ]);
        }
    }
}
