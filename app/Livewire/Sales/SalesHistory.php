<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Inventory;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class SalesHistory extends Component
{
    use WithPagination;
    use Toast;

    public $showDetailsModal = false;
    public $selectedSale = null;

    // Search and filters
    public $search = '';
    public $customerFilter = '';
    public $warehouseFilter = '';
    public $userFilter = '';
    public $statusFilter = '';
    public $paymentMethodFilter = '';
    public $dateFilter = '';
    public $startDate = '';
    public $endDate = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $sales = Sale::with(['customer', 'warehouse', 'user', 'items'])
            ->when($this->getFiltersQuery(), fn($q) => $this->applyFilters($q))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        $filterOptions = [
            'statuses' => [
                ['id' => '', 'name' => 'All Status'],
                ['id' => 'draft', 'name' => 'Draft'],
                ['id' => 'completed', 'name' => 'Completed'],
                ['id' => 'cancelled', 'name' => 'Cancelled'],
                ['id' => 'refunded', 'name' => 'Refunded'],
            ],
            'paymentMethods' => [
                ['id' => '', 'name' => 'All Methods'],
                ['id' => 'cash', 'name' => 'Cash'],
                ['id' => 'card', 'name' => 'Credit/Debit Card'],
                ['id' => 'gcash', 'name' => 'GCash'],
                ['id' => 'bank_transfer', 'name' => 'Bank Transfer'],
            ],
            'dates' => [
                ['id' => '', 'name' => 'All Time'],
                ['id' => 'today', 'name' => 'Today'],
                ['id' => 'yesterday', 'name' => 'Yesterday'],
                ['id' => 'week', 'name' => 'This Week'],
                ['id' => 'month', 'name' => 'This Month'],
                ['id' => 'custom', 'name' => 'Custom Range'],
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
            'customers' => $customers,
            'warehouses' => $warehouses,
            'users' => $users,
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

    public function printInvoice($saleId)
    {
        try {
            // Find the sale
            $sale = Sale::find($saleId);

            if (!$sale) {
                $this->error('Sale not found.');
                return;
            }

            if ($sale->status !== 'completed') {
                $this->error('Only completed sales can be printed.');
                return;
            }

            // Generate the download URL
            $downloadUrl = route('invoice.download', $sale->id);

            // Dispatch browser event to trigger download - Correct Livewire 3 syntax
            $this->dispatch(
                'trigger-download',
                url: $downloadUrl,
                filename: 'invoice-' . $sale->invoice_number . '.pdf'
            );

            $this->success('Invoice download started!');
        } catch (\Exception $e) {
            \Log::error('Print invoice error: ' . $e->getMessage());
            $this->error('Error generating invoice: ' . $e->getMessage());
        }
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
                $inventory = Inventory::where('product_id', $item->product_id)
                    ->where('warehouse_id', $sale->warehouse_id)
                    ->first();

                if ($inventory) {
                    $oldQuantity = $inventory->quantity_on_hand;
                    $newQuantity = $oldQuantity + $item->quantity;

                    $inventory->update(['quantity_on_hand' => $newQuantity]);

                    // Create stock movement
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $sale->warehouse_id,
                        'type' => 'return', // Using correct enum value
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
            ->when($this->dateFilter, function ($query) {
                switch ($this->dateFilter) {
                    case 'today':
                        return $query->whereDate('created_at', now());
                    case 'yesterday':
                        return $query->whereDate('created_at', now()->subDay());
                    case 'week':
                        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    case 'month':
                        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    case 'custom':
                        return $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
                }
            });
    }
}
