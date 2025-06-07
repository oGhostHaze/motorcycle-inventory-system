<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\LowStockAlert;
use App\Models\Inventory;
use App\Models\StockMovement;
use Livewire\Component;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $todaysSales = 0;
    public $monthSales = 0;
    public $totalProducts = 0;
    public $lowStockItems = 0;
    public $totalCustomers = 0;
    public $pendingOrders = 0;
    public $totalInventoryValue = 0;
    public $recentSales = [];
    public $topProducts = [];
    public $lowStockProducts = [];
    public $recentStockMovements = [];
    public $salesChartData = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Today's sales
        $this->todaysSales = Sale::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_amount');

        // This month's sales
        $this->monthSales = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Total products
        $this->totalProducts = Product::where('status', 'active')->count();

        // Low stock items
        $this->lowStockItems = LowStockAlert::where('status', 'active')->count();

        // Total customers
        $this->totalCustomers = Customer::where('is_active', true)->count();

        // Pending purchase orders
        $this->pendingOrders = PurchaseOrder::where('status', 'pending')->count();

        // Total inventory value
        $this->totalInventoryValue = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(inventories.quantity_on_hand * products.cost_price) as total_value')
            ->value('total_value') ?? 0;

        // Recent sales (last 5)
        $this->recentSales = Sale::with(['customer', 'user'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top selling products (this month)
        $this->topProducts = Product::withCount(['stockMovements as sales_count' => function ($query) {
            $query->where('type', 'sale')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }])
            ->having('sales_count', '>', 0)
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get();

        // Low stock products
        $this->lowStockProducts = Product::with('inventory')
            ->whereHas('inventory', function ($query) {
                $query->whereRaw('quantity_on_hand <= min_stock_level');
            })
            ->limit(5)
            ->get();

        // Recent stock movements
        $this->recentStockMovements = StockMovement::with(['product', 'warehouse', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Sales chart data (last 7 days)
        $this->salesChartData = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            $sales = Sale::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');

            return [
                'date' => $date->format('M d'),
                'sales' => (float) $sales
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'todaysSales' => number_format($this->todaysSales, 2),
            'monthSales' => number_format($this->monthSales, 2),
            'totalInventoryValue' => number_format($this->totalInventoryValue, 2),
        ]);
    }

    public function refreshData()
    {
        $this->loadDashboardData();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Dashboard data refreshed!'
        ]);
    }
}
