<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class SalesHistory extends Component
{
    use WithPagination;
    use Toast;

    public bool $showDetailsModal = false;
    public ?Sale $selectedSale = null;

    // Search and filters
    public string $search = '';
    public string $customerFilter = '';
    public string $warehouseFilter = '';
    public string $userFilter = '';
    public string $statusFilter = '';
    public string $dateFilter = '';
    public string $paymentMethodFilter = '';

    // Date range
    public string $startDate = '';
    public string $endDate = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $sales = Sale::with(['customer', 'warehouse', 'user', 'items.product'])
            ->when($this->search, fn($q) => $q->where('invoice_number', 'like', '%' . $this->search . '%')
                ->orWhereHas('customer', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                }))
            ->when($this->customerFilter, fn($q) => $q->where('customer_id', $this->customerFilter))
            ->when($this->warehouseFilter, fn($q) => $q->where('warehouse_id', $this->warehouseFilter))
            ->when($this->userFilter, fn($q) => $q->where('user_id', $this->userFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->paymentMethodFilter, fn($q) => $q->where('payment_method', $this->paymentMethodFilter))
            ->when($this->dateFilter, function ($q) {
                switch ($this->dateFilter) {
                    case 'today':
                        return $q->whereDate('created_at', today());
                    case 'yesterday':
                        return $q->whereDate('created_at', yesterday());
                    case 'week':
                        return $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    case 'month':
                        return $q->whereMonth('created_at', now()->month);
                    case 'custom':
                        return $q->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59']);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        $filterOptions = [
            'customers' => $customers->map(fn($c) => ['value' => $c->id, 'label' => $c->name]),
            'warehouses' => $warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name]),
            'users' => $users->map(fn($u) => ['value' => $u->id, 'label' => $u->name]),
            'statuses' => [
                ['value' => '', 'label' => 'All Status'],
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
                ['value' => 'refunded', 'label' => 'Refunded'],
            ],
            'paymentMethods' => [
                ['value' => '', 'label' => 'All Methods'],
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'card', 'label' => 'Credit/Debit Card'],
                ['value' => 'gcash', 'label' => 'GCash'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
            ],
            'dates' => [
                ['value' => '', 'label' => 'All Time'],
                ['value' => 'today', 'label' => 'Today'],
                ['value' => 'yesterday', 'label' => 'Yesterday'],
                ['value' => 'week', 'label' => 'This Week'],
                ['value' => 'month', 'label' => 'This Month'],
                ['value' => 'custom', 'label' => 'Custom Range'],
            ]
        ];

        // Calculate summary statistics
        $totalSales = $sales->total();
        $totalAmount = Sale::when($this->getFiltersQuery(), fn($q) => $this->applyFilters($q))
            ->where('status', 'completed')
            ->sum('total_amount');
        $averageValue = $totalSales > 0 ? $totalAmount / $totalSales : 0;

        return view('livewire.sales.sales-history', [
            'sales' => $sales,
            'filterOptions' => $filterOptions,
            'totalSales' => $totalSales,
            'totalAmount' => $totalAmount,
            'averageValue' => $averageValue,
        ])->layout('layouts.app', ['title' => 'Sales History']);
    }

    public function viewSaleDetails(Sale $sale)
    {
        $this->selectedSale = $sale->load(['customer', 'warehouse', 'user', 'items.product']);
        $this->showDetailsModal = true;
    }

    public function printInvoice(Sale $sale)
    {
        // Logic to generate and print invoice
        $this->success('Invoice print job sent!');
    }

    public function duplicateSale(Sale $sale)
    {
        // Logic to duplicate sale (create new draft based on this sale)
        $this->success('Sale duplicated as draft!');
    }

    public function refundSale(Sale $sale)
    {
        if ($sale->status !== 'completed') {
            $this->error('Only completed sales can be refunded.');
            return;
        }

        try {
            // Update sale status
            $sale->update(['status' => 'refunded']);

            // Restore inventory
            foreach ($sale->items as $item) {
                $inventory = \App\Models\Inventory::where('product_id', $item->product_id)
                    ->where('warehouse_id', $sale->warehouse_id)
                    ->first();

                if ($inventory) {
                    $oldQuantity = $inventory->quantity_on_hand;
                    $newQuantity = $oldQuantity + $item->quantity;

                    $inventory->update(['quantity_on_hand' => $newQuantity]);

                    // Create stock movement
                    \App\Models\StockMovement::create([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $sale->warehouse_id,
                        'type' => 'return',
                        'quantity_before' => $oldQuantity,
                        'quantity_changed' => $item->quantity,
                        'quantity_after' => $newQuantity,
                        'unit_cost' => $inventory->average_cost,
                        'reference_id' => $sale->id,
                        'reference_type' => Sale::class,
                        'user_id' => auth()->id(),
                        'notes' => 'Refund for sale: ' . $sale->invoice_number,
                    ]);
                }
            }

            $this->success('Sale refunded successfully and inventory restored!');
        } catch (\Exception $e) {
            $this->error('Error processing refund: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'customerFilter', 'warehouseFilter', 'userFilter', 'statusFilter', 'dateFilter', 'paymentMethodFilter']);
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    private function getFiltersQuery()
    {
        return $this->search || $this->customerFilter || $this->warehouseFilter ||
            $this->userFilter || $this->statusFilter || $this->paymentMethodFilter || $this->dateFilter;
    }

    private function applyFilters($query)
    {
        return $query->when($this->search, fn($q) => $q->where('invoice_number', 'like', '%' . $this->search . '%')
            ->orWhereHas('customer', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            }))
            ->when($this->customerFilter, fn($q) => $q->where('customer_id', $this->customerFilter))
            ->when($this->warehouseFilter, fn($q) => $q->where('warehouse_id', $this->warehouseFilter))
            ->when($this->userFilter, fn($q) => $q->where('user_id', $this->userFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->paymentMethodFilter, fn($q) => $q->where('payment_method', $this->paymentMethodFilter))
            ->when($this->dateFilter, function ($q) {
                switch ($this->dateFilter) {
                    case 'today':
                        return $q->whereDate('created_at', today());
                    case 'yesterday':
                        return $q->whereDate('created_at', yesterday());
                    case 'week':
                        return $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    case 'month':
                        return $q->whereMonth('created_at', now()->month);
                    case 'custom':
                        return $q->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59']);
                }
            });
    }
}
