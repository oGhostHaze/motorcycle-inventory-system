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
    // Existing properties
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

    // NEW PROFIT TRACKING PROPERTIES
    public $todaysProfit = 0;
    public $monthProfit = 0;
    public $yearProfit = 0;
    public $todaysProfitMargin = 0;
    public $monthProfitMargin = 0;
    public $yearProfitMargin = 0;
    public $topProfitProducts = [];
    public $profitByCategory = [];
    public $profitTrend = [];
    public $averageTransactionProfit = 0;
    public $totalCostOfGoodsSold = 0;

    // Existing arrays
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

    public function refreshData()
    {
        $this->loadDashboardData();

        // Properly dispatch the event to refresh charts
        $this->dispatch('refresh-charts');

        // Show success notification
        session()->flash('success', 'Dashboard data refreshed successfully!');
    }

    public function loadDashboardData()
    {
        // Basic stats (existing)
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

        // NEW: PROFIT CALCULATIONS
        $this->calculateProfitMetrics();

        // Existing calculations continue...
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

        // Load other data
        $this->loadRecentSales();
        $this->loadTopProducts();
        $this->loadLowStockProducts();
        $this->loadRecentStockMovements();
        $this->loadChartData();
        $this->loadCategoryDistribution();
        $this->loadTopCustomers();
        $this->loadStockStatusData();
        $this->loadSalesByPaymentMethod();
        $this->loadWeeklyTrend();

        // NEW: Load profit-specific data
        $this->loadTopProfitProducts();
        $this->loadProfitByCategory();
        $this->loadProfitTrend();
    }

    // Existing methods (keep all the existing loadXXX methods)
    private function loadRecentSales()
    {
        $this->recentSales = Sale::with(['customer', 'user'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function loadTopProducts()
    {
        $this->topProducts = Product::with(['saleItems' => function ($query) {
            $query->whereHas('sale', function ($q) {
                $q->where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            });
        }])
            ->get()
            ->map(function ($product) {
                $totalSold = 0;
                $totalRevenue = 0;

                foreach ($product->saleItems as $saleItem) {
                    $totalSold += $saleItem->quantity;
                    $totalRevenue += $saleItem->total_price;
                }

                $product->total_sold = $totalSold;
                $product->revenue = $totalRevenue;

                return $product;
            })
            ->filter(function ($product) {
                return $product->total_sold > 0;
            })
            ->sortByDesc('total_sold')
            ->take(5)
            ->values();
    }

    private function loadLowStockProducts()
    {
        $this->lowStockProducts = Product::with(['inventory', 'category'])
            ->whereHas('inventory', function ($query) {
                $query->havingRaw('SUM(quantity_on_hand) <= min_stock_level');
            })
            ->orderBy('min_stock_level', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                $product->current_stock = $product->inventory->sum('quantity_on_hand');
                return $product;
            });
    }

    private function loadRecentStockMovements()
    {
        $this->recentStockMovements = StockMovement::with(['product', 'warehouse', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function loadChartData()
    {
        $this->salesChartData = collect(range(29, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            $sales = Sale::whereDate('created_at', $date->format('Y-m-d'))
                ->where('status', 'completed')
                ->sum('total_amount');

            return [
                'date' => $date->format('M j'),
                'sales' => $sales,
                'formatted_sales' => number_format($sales, 2),
            ];
        });
    }

    private function loadCategoryDistribution()
    {
        $this->categoryDistribution = Category::withCount(['products' => function ($query) {
            $query->where('status', 'active');
        }])
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->limit(8)
            ->get();
    }

    private function loadTopCustomers()
    {
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
    }

    private function loadStockStatusData()
    {
        $totalProducts = Product::where('status', 'active')->count();
        $inStock = Product::whereHas('inventory', function ($query) {
            $query->where('quantity_on_hand', '>', 0);
        })->count();
        $lowStock = Product::whereHas('inventory', function ($query) {
            $query->havingRaw('SUM(quantity_on_hand) <= min_stock_level');
        })->count();
        $outOfStock = $totalProducts - $inStock;

        $this->stockStatusData = [
            ['status' => 'In Stock', 'count' => $inStock, 'color' => '#10b981'],
            ['status' => 'Low Stock', 'count' => $lowStock, 'color' => '#f59e0b'],
            ['status' => 'Out of Stock', 'count' => $outOfStock, 'color' => '#ef4444'],
        ];
    }

    private function loadSalesByPaymentMethod()
    {
        $this->salesByPaymentMethod = Sale::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('payment_method')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->get()
            ->map(function ($item) {
                $item->payment_method_display = match ($item->payment_method) {
                    'cash' => 'Cash',
                    'credit_card' => 'Credit Card',
                    'debit_card' => 'Debit Card',
                    'bank_transfer' => 'Bank Transfer',
                    'gcash' => 'GCash',
                    'maya' => 'Maya',
                    default => ucfirst(str_replace('_', ' ', $item->payment_method)),
                };
                return $item;
            });
    }

    private function loadWeeklyTrend()
    {
        $this->weeklyTrend = collect(range(6, 0))->map(function ($weeksAgo) {
            $startDate = now()->subWeeks($weeksAgo)->startOfWeek();
            $endDate = now()->subWeeks($weeksAgo)->endOfWeek();

            $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('total_amount');

            return [
                'week' => 'Week ' . (7 - $weeksAgo),
                'sales' => $sales,
                'start_date' => $startDate->format('M j'),
                'end_date' => $endDate->format('M j'),
            ];
        });
    }

    private function loadMonthlyChartData()
    {
        $this->monthlyChartData = collect(range(11, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            $sales = Sale::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->where('status', 'completed')
                ->sum('total_amount');

            return [
                'month' => $date->format('F'),
                'short_month' => $date->format('M'),
                'sales' => $sales,
                'formatted_sales' => number_format($sales, 2),
            ];
        });
    }

    /**
     * NEW METHOD: Calculate comprehensive profit metrics (SAFE VERSION)
     */
    private function calculateProfitMetrics()
    {
        // Today's Profit
        $todaysSaleItems = SaleItem::whereHas('sale', function ($q) {
            $q->whereDate('created_at', today())->where('status', 'completed');
        })->with('product')->get();

        $this->todaysProfit = $todaysSaleItems->sum(function ($item) {
            // Use cost_price from sale_item if available, otherwise fallback to product cost_price
            $costPrice = $item->cost_price ?? $item->product->cost_price ?? 0;
            return ($item->unit_price - $costPrice) * $item->quantity;
        });

        // Month's Profit
        $monthSaleItems = SaleItem::whereHas('sale', function ($q) {
            $q->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'completed');
        })->with('product')->get();

        $this->monthProfit = $monthSaleItems->sum(function ($item) {
            $costPrice = $item->cost_price ?? $item->product->cost_price ?? 0;
            return ($item->unit_price - $costPrice) * $item->quantity;
        });

        // Year's Profit
        $yearSaleItems = SaleItem::whereHas('sale', function ($q) {
            $q->whereYear('created_at', now()->year)
                ->where('status', 'completed');
        })->with('product')->get();

        $this->yearProfit = $yearSaleItems->sum(function ($item) {
            $costPrice = $item->cost_price ?? $item->product->cost_price ?? 0;
            return ($item->unit_price - $costPrice) * $item->quantity;
        });

        // Cost of Goods Sold (Year)
        $this->totalCostOfGoodsSold = $yearSaleItems->sum(function ($item) {
            $costPrice = $item->cost_price ?? $item->product->cost_price ?? 0;
            return $costPrice * $item->quantity;
        });

        // Profit Margins
        $this->todaysProfitMargin = $this->todaysSales > 0 ? ($this->todaysProfit / $this->todaysSales) * 100 : 0;
        $this->monthProfitMargin = $this->monthSales > 0 ? ($this->monthProfit / $this->monthSales) * 100 : 0;
        $this->yearProfitMargin = $this->yearSales > 0 ? ($this->yearProfit / $this->yearSales) * 100 : 0;

        // Average Transaction Profit
        $completedSalesCount = Sale::whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->count();

        $this->averageTransactionProfit = $completedSalesCount > 0 ? $this->yearProfit / $completedSalesCount : 0;
    }

    /**
     * NEW METHOD: Load top profit-generating products (SAFE VERSION)
     */
    private function loadTopProfitProducts()
    {
        $this->topProfitProducts = Product::with(['saleItems' => function ($query) {
            $query->whereHas('sale', function ($q) {
                $q->where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            });
        }])
            ->get()
            ->map(function ($product) {
                // Calculate profit and quantity sold manually
                $totalProfit = 0;
                $totalSold = 0;

                foreach ($product->saleItems as $saleItem) {
                    // Use cost_price from sale_item if available, otherwise fallback to product cost_price
                    $costPrice = $saleItem->cost_price ?? $product->cost_price ?? 0;
                    $totalProfit += ($saleItem->unit_price - $costPrice) * $saleItem->quantity;
                    $totalSold += $saleItem->quantity;
                }

                $product->total_profit = $totalProfit;
                $product->total_sold = $totalSold;
                $product->profit_margin = $product->selling_price > 0
                    ? (($product->selling_price - ($product->cost_price ?? 0)) / $product->selling_price) * 100
                    : 0;

                return $product;
            })
            ->filter(function ($product) {
                return $product->total_profit > 0;
            })
            ->sortByDesc('total_profit')
            ->take(5)
            ->values();
    }

    /**
     * NEW METHOD: Load profit breakdown by category (SAFE VERSION)
     */
    private function loadProfitByCategory()
    {
        $this->profitByCategory = Category::with('products')
            ->get()
            ->map(function ($category) {
                // Calculate profit for this category manually
                $categoryProfit = 0;

                foreach ($category->products as $product) {
                    $productProfit = SaleItem::whereHas('sale', function ($q) {
                        $q->where('status', 'completed')
                            ->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year);
                    })
                        ->where('product_id', $product->id)
                        ->get()
                        ->sum(function ($item) use ($product) {
                            $costPrice = $item->cost_price ?? $product->cost_price ?? 0;
                            return ($item->unit_price - $costPrice) * $item->quantity;
                        });

                    $categoryProfit += $productProfit;
                }

                $category->total_profit = $categoryProfit;
                return $category;
            })
            ->filter(function ($category) {
                return $category->total_profit > 0;
            })
            ->sortByDesc('total_profit')
            ->take(8)
            ->values();
    }

    /**
     * NEW METHOD: Load 30-day profit trend (SAFE VERSION)
     */
    private function loadProfitTrend()
    {
        $this->profitTrend = collect(range(29, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);

            $profit = SaleItem::whereHas('sale', function ($q) use ($date) {
                $q->whereDate('created_at', $date->format('Y-m-d'))
                    ->where('status', 'completed');
            })->with('product')->get()->sum(function ($item) {
                $costPrice = $item->cost_price ?? $item->product->cost_price ?? 0;
                return ($item->unit_price - $costPrice) * $item->quantity;
            });

            $revenue = Sale::whereDate('created_at', $date->format('Y-m-d'))
                ->where('status', 'completed')
                ->sum('total_amount');

            return [
                'date' => $date->format('M j'),
                'profit' => $profit,
                'revenue' => $revenue,
                'margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0
            ];
        });
    }

    public function render()
    {
        return view('livewire.dashboard')->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
