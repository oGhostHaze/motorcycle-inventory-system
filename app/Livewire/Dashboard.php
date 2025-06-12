<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\LowStockAlert;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\SaleItem;
use App\Models\Category;
use App\Models\Supplier;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $todaysSales = 0;
    public $monthSales = 0;
    public $yearSales = 0;
    public $totalProducts = 0;
    public $lowStockItems = 0;
    public $totalCustomers = 0;
    public $pendingOrders = 0;
    public $totalInventoryValue = 0;
    public $totalSuppliers = 0;
    public $totalCategories = 0;
    public $monthlyGrowth = 0;
    public $recentSales = [];
    public $topProducts = [];
    public $lowStockProducts = [];
    public $recentStockMovements = [];
    public $salesChartData = [];
    public $monthlyChartData = [];
    public $categoryDistribution = [];
    public $topCustomers = [];
    public $stockStatusData = [];
    public $salesByPaymentMethod = [];
    public $weeklyTrend = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Basic stats
        $this->todaysSales = Sale::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_amount');

        $this->monthSales = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        $this->yearSales = Sale::whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        $this->totalProducts = Product::where('status', 'active')->count();
        $this->lowStockItems = LowStockAlert::where('status', 'active')->count();
        $this->totalCustomers = Customer::where('is_active', true)->count();
        $this->pendingOrders = PurchaseOrder::where('status', 'pending')->count();
        $this->totalSuppliers = Supplier::where('is_active', true)->count();
        $this->totalCategories = Category::where('is_active', true)->count();

        $this->totalInventoryValue = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(inventories.quantity_on_hand * products.cost_price) as total_value')
            ->value('total_value') ?? 0;

        // Calculate monthly growth
        $lastMonthSales = Sale::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        $this->monthlyGrowth = $lastMonthSales > 0
            ? (($this->monthSales - $lastMonthSales) / $lastMonthSales) * 100
            : 0;

        // Recent sales (last 10)
        $this->recentSales = Sale::with(['customer', 'user'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top selling products (this month) - Fixed query
        $this->topProducts = Product::withSum(['saleItems as total_sold' => function ($query) {
            $query->whereHas('sale', function ($q) {
                $q->where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            });
        }], 'quantity')
            ->withSum(['saleItems as total_revenue' => function ($query) {
                $query->whereHas('sale', function ($q) {
                    $q->where('status', 'completed')
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                });
            }], 'total_price')
            ->having('total_sold', '>', 0)
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                $product->revenue = $product->total_revenue ?? 0;
                return $product;
            });

        // Low stock products - Fixed query
        $this->lowStockProducts = Product::with('inventory')
            ->whereHas('inventory', function ($query) {
                $query->whereRaw('quantity_on_hand <= min_stock_level');
            })
            ->take(5)
            ->get();

        // Recent stock movements
        $this->recentStockMovements = StockMovement::with(['product', 'warehouse', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Sales chart data (last 30 days)
        $this->salesChartData = collect(range(29, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            $sales = Sale::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');

            return [
                'date' => $date->format('M d'),
                'sales' => (float) $sales,
                'day' => $date->format('D'),
                'full_date' => $date->format('Y-m-d')
            ];
        })->toArray();

        // Monthly chart data (last 12 months)
        $this->monthlyChartData = collect(range(11, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            $sales = Sale::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->where('status', 'completed')
                ->sum('total_amount');

            return [
                'month' => $date->format('M Y'),
                'sales' => (float) $sales,
                'short_month' => $date->format('M')
            ];
        })->toArray();

        // Category distribution - Fixed query
        $this->categoryDistribution = Category::withCount(['products as total_products' => function ($query) {
            $query->where('status', 'active');
        }])
            ->where('is_active', true)
            ->orderBy('total_products', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($category) {
                // Calculate total inventory value for this category
                $totalValue = $category->products()
                    ->where('status', 'active')
                    ->join('inventories', 'products.id', '=', 'inventories.product_id')
                    ->sum(\DB::raw('inventories.quantity_on_hand * products.selling_price'));

                return [
                    'name' => $category->name,
                    'products' => $category->total_products,
                    'value' => $totalValue ?? 0,
                    'color' => $this->getRandomColor()
                ];
            })->toArray();

        // Top customers (this month)
        $this->topCustomers = Customer::withSum(['sales as total_spent' => function ($query) {
            $query->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }], 'total_amount')
            ->withCount(['sales as total_orders' => function ($query) {
                $query->where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            }])
            ->having('total_spent', '>', 0)
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();

        // Stock status distribution - Fixed query
        $totalProducts = Product::where('status', 'active')->count();

        // Calculate stock statuses more efficiently
        $products = Product::with('inventory')->where('status', 'active')->get();
        $inStock = 0;
        $lowStock = 0;
        $outOfStock = 0;

        foreach ($products as $product) {
            $totalStock = $product->inventory->sum('quantity_on_hand');
            if ($totalStock == 0) {
                $outOfStock++;
            } elseif ($totalStock <= $product->min_stock_level) {
                $lowStock++;
            } else {
                $inStock++;
            }
        }

        $this->stockStatusData = [
            ['status' => 'In Stock', 'count' => $inStock, 'color' => '#10b981'],
            ['status' => 'Low Stock', 'count' => $lowStock, 'color' => '#f59e0b'],
            ['status' => 'Out of Stock', 'count' => $outOfStock, 'color' => '#ef4444'],
        ];

        // Sales by payment method (this month)
        $this->salesByPaymentMethod = Sale::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('payment_method')
            ->selectRaw('payment_method, SUM(total_amount) as total, COUNT(*) as count')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => ucfirst(str_replace('_', ' ', $item->payment_method)),
                    'total' => $item->total,
                    'count' => $item->count,
                    'color' => $this->getRandomColor()
                ];
            })->toArray();

        // Weekly trend (last 4 weeks)
        $this->weeklyTrend = collect(range(3, 0))->map(function ($weeksAgo) {
            $startOfWeek = now()->subWeeks($weeksAgo)->startOfWeek();
            $endOfWeek = now()->subWeeks($weeksAgo)->endOfWeek();

            $sales = Sale::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->where('status', 'completed')
                ->sum('total_amount');

            return [
                'week' => 'Week ' . ($weeksAgo + 1),
                'sales' => (float) $sales,
                'period' => $startOfWeek->format('M d') . ' - ' . $endOfWeek->format('M d')
            ];
        })->reverse()->values()->toArray();
    }

    private function getRandomColor()
    {
        $colors = [
            '#3b82f6',
            '#ef4444',
            '#10b981',
            '#f59e0b',
            '#8b5cf6',
            '#06b6d4',
            '#84cc16',
            '#f97316',
            '#ec4899',
            '#6366f1',
            '#14b8a6',
            '#eab308'
        ];
        return $colors[array_rand($colors)];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }

    public function refreshData()
    {
        $this->loadDashboardData();
        $this->dispatch('refreshCharts');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Dashboard data refreshed!'
        ]);
    }

    public function getChartData($type)
    {
        switch ($type) {
            case 'sales':
                return $this->salesChartData;
            case 'monthly':
                return $this->monthlyChartData;
            case 'categories':
                return $this->categoryDistribution;
            case 'stock-status':
                return $this->stockStatusData;
            case 'payment-methods':
                return $this->salesByPaymentMethod;
            case 'weekly':
                return $this->weeklyTrend;
            default:
                return [];
        }
    }
}
