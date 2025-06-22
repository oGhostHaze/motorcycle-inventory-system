<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Category;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mary\Traits\Toast;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesReports extends Component
{
    use Toast;

    // Filter Properties
    public $dateRange = 'today';
    public $startDate = '';
    public $endDate = '';
    public $warehouseFilter = '';
    public $customerFilter = '';
    public $userFilter = '';
    public $productFilter = '';
    public $categoryFilter = '';
    public $paymentMethodFilter = '';
    public $statusFilter = 'completed';

    // Report Type
    public $reportType = 'overview';

    // Data Properties
    public $salesSummary = [];
    public $profitSummary = [];
    public $topProducts = [];
    public $topCustomers = [];
    public $salesTrends = [];
    public $paymentMethods = [];
    public $hourlyTrends = [];
    public $salesByUser = [];
    public $categoryPerformance = [];
    public $dailyComparison = [];

    // Chart Data
    public $salesChartData = [];
    public $profitChartData = [];
    public $trendsChartData = [];

    // Export Properties
    public $showExportModal = false;
    public $exportFormat = 'excel';
    public $exportDateRange = '';

    public function mount()
    {
        $this->initializeDates();
        $this->loadReportData();
    }

    private function initializeDates()
    {
        switch ($this->dateRange) {
            case 'today':
                $this->startDate = now()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case 'week':
                $this->startDate = now()->startOfWeek()->format('Y-m-d');
                $this->endDate = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = now()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $this->startDate = now()->startOfQuarter()->format('Y-m-d');
                $this->endDate = now()->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = now()->startOfYear()->format('Y-m-d');
                $this->endDate = now()->endOfYear()->format('Y-m-d');
                break;
            case 'custom':
                if (empty($this->startDate)) {
                    $this->startDate = now()->startOfMonth()->format('Y-m-d');
                }
                if (empty($this->endDate)) {
                    $this->endDate = now()->format('Y-m-d');
                }
                break;
        }
    }

    public function updatedDateRange()
    {
        $this->initializeDates();
        $this->loadReportData();
    }

    public function updatedStartDate()
    {
        if ($this->dateRange === 'custom') {
            $this->loadReportData();
        }
    }

    public function updatedEndDate()
    {
        if ($this->dateRange === 'custom') {
            $this->loadReportData();
        }
    }

    public function applyFilters()
    {
        $this->loadReportData();
        $this->dispatch('refresh-charts', [
            'trendsChartData' => $this->trendsChartData,
            'salesChartData' => $this->salesChartData,
        ]);
        $this->success('Filters applied successfully!');
    }

    public function resetFilters()
    {
        $this->warehouseFilter = '';
        $this->customerFilter = '';
        $this->userFilter = '';
        $this->productFilter = '';
        $this->categoryFilter = '';
        $this->paymentMethodFilter = '';
        $this->statusFilter = 'completed';
        $this->dateRange = 'month';
        $this->initializeDates();
        $this->loadReportData();
        $this->success('Filters reset successfully!');
    }

    public function loadReportData()
    {
        $this->loadSalesSummary();
        $this->loadProfitSummary();
        $this->loadTopProducts();
        $this->loadTopCustomers();
        $this->loadSalesTrends();
        $this->loadPaymentMethods();
        $this->loadHourlyTrends();
        $this->loadSalesByUser();
        $this->loadCategoryPerformance();
        $this->loadDailyComparison();
        $this->prepareChartData();
    }

    private function getBaseQuery()
    {
        return Sale::with(['customer', 'warehouse', 'user', 'items.product.category'])
            ->whereBetween('sales.created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->when($this->warehouseFilter, fn($q) => $q->where('warehouse_id', $this->warehouseFilter))
            ->when($this->customerFilter, fn($q) => $q->where('customer_id', $this->customerFilter))
            ->when($this->userFilter, fn($q) => $q->where('user_id', $this->userFilter))
            ->when($this->paymentMethodFilter, fn($q) => $q->where('payment_method', $this->paymentMethodFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter));
    }

    private function loadSalesSummary()
    {
        $sales = $this->getBaseQuery()->get();

        $this->salesSummary = [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'average_sale' => $sales->count() > 0 ? $sales->avg('total_amount') : 0,
            'cash_sales' => $sales->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => $sales->where('payment_method', 'card')->sum('total_amount'),
            'gcash_sales' => $sales->where('payment_method', 'gcash')->sum('total_amount'),
            'bank_transfer_sales' => $sales->where('payment_method', 'bank_transfer')->sum('total_amount'),
            'total_items_sold' => $sales->sum(function ($sale) {
                return $sale->items->sum('quantity');
            }),
            'unique_customers' => $sales->pluck('customer_id')->filter()->unique()->count(),
        ];
    }

    private function loadProfitSummary()
    {
        $salesWithItems = $this->getBaseQuery()->with('items.product')->get();

        $totalProfit = 0;
        $totalCost = 0;
        $totalRevenue = 0;

        foreach ($salesWithItems as $sale) {
            foreach ($sale->items as $item) {
                $cost = ($item->cost_price ?? $item->product->cost_price ?? 0) * $item->quantity;
                $revenue = $item->total_price;

                $totalCost += $cost;
                $totalRevenue += $revenue;
                $totalProfit += ($revenue - $cost);
            }
        }

        $this->profitSummary = [
            'total_profit' => $totalProfit,
            'total_cost' => $totalCost,
            'total_revenue' => $totalRevenue,
            'profit_margin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0,
            'average_profit_per_sale' => $salesWithItems->count() > 0 ? $totalProfit / $salesWithItems->count() : 0,
        ];
    }

    private function loadTopProducts()
    {
        $query = SaleItem::query()
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'products.cost_price',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('AVG(sale_items.unit_price) as avg_price'),
                DB::raw('COUNT(DISTINCT sale_items.sale_id) as total_sales')
            ])
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->when($this->warehouseFilter, fn($q) => $q->where('sales.warehouse_id', $this->warehouseFilter))
            ->when($this->statusFilter, fn($q) => $q->where('sales.status', $this->statusFilter))
            ->groupBy(['products.id', 'products.name', 'products.sku', 'products.cost_price']);

        $this->topProducts = $query->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $profit = $item->total_revenue - ($item->cost_price * $item->total_quantity);
                $profitMargin = $item->total_revenue > 0 ? ($profit / $item->total_revenue) * 100 : 0;

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'quantity' => $item->total_quantity,
                    'revenue' => $item->total_revenue,
                    'avg_price' => $item->avg_price,
                    'total_sales' => $item->total_sales,
                    'profit' => $profit,
                    'profit_margin' => $profitMargin,
                ];
            })
            ->toArray();
    }

    private function loadTopCustomers()
    {
        $this->topCustomers = $this->getBaseQuery()
            ->select([
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.phone',
                DB::raw('COUNT(sales.id) as total_orders'),
                DB::raw('SUM(sales.total_amount) as total_spent'),
                DB::raw('AVG(sales.total_amount) as avg_order_value'),
                DB::raw('MAX(sales.created_at) as last_order_date')
            ])
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->groupBy(['customers.id', 'customers.name', 'customers.email', 'customers.phone'])
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function loadSalesTrends()
    {
        $days = Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1;

        if ($days <= 31) {
            // Daily trends for periods up to 31 days
            $this->salesTrends = $this->getBaseQuery()
                ->select([
                    DB::raw('DATE(sales.created_at) as date'),
                    DB::raw('COUNT(*) as sales_count'),
                    DB::raw('SUM(total_amount) as total_amount')
                ])
                ->groupBy(DB::raw('DATE(sales.created_at)'))
                ->orderBy('date')
                ->get()
                ->toArray();
        } else {
            // Weekly trends for longer periods
            $this->salesTrends = $this->getBaseQuery()
                ->select([
                    DB::raw('YEARWEEK(sales.created_at) as week'),
                    DB::raw('MIN(DATE(sales.created_at)) as week_start'),
                    DB::raw('MAX(DATE(sales.created_at)) as week_end'),
                    DB::raw('COUNT(*) as sales_count'),
                    DB::raw('SUM(total_amount) as total_amount')
                ])
                ->groupBy(DB::raw('YEARWEEK(sales.created_at)'))
                ->orderBy('week')
                ->get()
                ->toArray();
        }
    }

    private function loadPaymentMethods()
    {
        $this->paymentMethods = $this->getBaseQuery()
            ->select([
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('AVG(total_amount) as avg_amount')
            ])
            ->groupBy('payment_method')
            ->orderBy('total_amount', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => ucfirst(str_replace('_', ' ', $item->payment_method)),
                    'count' => $item->count,
                    'total_amount' => $item->total_amount,
                    'avg_amount' => $item->avg_amount,
                    'percentage' => 0, // Will be calculated in the view
                ];
            })
            ->toArray();
    }

    private function loadHourlyTrends()
    {
        $this->hourlyTrends = $this->getBaseQuery()
            ->select([
                DB::raw('HOUR(sales.created_at) as hour'),
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(total_amount) as total_amount')
            ])
            ->groupBy(DB::raw('HOUR(sales.created_at)'))
            ->orderBy('hour')
            ->get()
            ->toArray();
    }

    private function loadSalesByUser()
    {
        $this->salesByUser = $this->getBaseQuery()
            ->select([
                'users.id',
                'users.name',
                'users.role',
                DB::raw('COUNT(sales.id) as total_sales'),
                DB::raw('SUM(sales.total_amount) as total_amount'),
                DB::raw('AVG(sales.total_amount) as avg_sale_amount')
            ])
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->groupBy(['users.id', 'users.name', 'users.role'])
            ->orderBy('total_amount', 'desc')
            ->get()
            ->toArray();
    }

    private function loadCategoryPerformance()
    {
        $this->categoryPerformance = SaleItem::query()
            ->select([
                'categories.id',
                'categories.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT sale_items.sale_id) as total_sales')
            ])
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->when($this->statusFilter, fn($q) => $q->where('sales.status', $this->statusFilter))
            ->groupBy(['categories.id', 'categories.name'])
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->toArray();
    }

    private function loadDailyComparison()
    {
        $currentPeriodStart = Carbon::parse($this->startDate);
        $currentPeriodEnd = Carbon::parse($this->endDate);
        $periodLength = $currentPeriodStart->diffInDays($currentPeriodEnd) + 1;

        $previousPeriodEnd = $currentPeriodStart->copy()->subDay();
        $previousPeriodStart = $previousPeriodEnd->copy()->subDays($periodLength - 1);

        $currentSales = Sale::whereBetween('sales.created_at', [
            $currentPeriodStart->startOfDay(),
            $currentPeriodEnd->endOfDay()
        ])->where('status', $this->statusFilter);

        $previousSales = Sale::whereBetween('sales.created_at', [
            $previousPeriodStart->startOfDay(),
            $previousPeriodEnd->endOfDay()
        ])->where('status', $this->statusFilter);

        $currentRevenue = $currentSales->sum('total_amount');
        $previousRevenue = $previousSales->sum('total_amount');
        $currentCount = $currentSales->count();
        $previousCount = $previousSales->count();

        $this->dailyComparison = [
            'current_revenue' => $currentRevenue,
            'previous_revenue' => $previousRevenue,
            'revenue_change' => $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0,
            'current_count' => $currentCount,
            'previous_count' => $previousCount,
            'count_change' => $previousCount > 0 ? (($currentCount - $previousCount) / $previousCount) * 100 : 0,
        ];
    }

    private function prepareChartData()
    {
        // Sales Chart Data
        $this->salesChartData = collect($this->salesTrends)->map(function ($trend) {
            return [
                'date' => isset($trend['date']) ? $trend['date'] : ($trend['week_start'] ?? ''),
                'sales' => $trend['total_amount'],
                'count' => $trend['sales_count'],
            ];
        })->toArray();

        // Profit Chart Data (simplified for demo)
        $this->profitChartData = $this->salesChartData;

        // Trends Chart Data
        $this->trendsChartData = collect($this->hourlyTrends)->map(function ($trend) {
            return [
                'hour' => $trend['hour'] . ':00',
                'sales' => $trend['total_amount'],
                'count' => $trend['sales_count'],
            ];
        })->toArray();
    }

    public function exportReport()
    {
        try {
            $filename = 'sales-report-' . $this->startDate . '-to-' . $this->endDate;

            if ($this->exportFormat === 'pdf') {
                return $this->exportToPdf($filename);
            } else {
                return $this->exportToExcel($filename);
            }
        } catch (\Exception $e) {
            $this->error('Export failed: ' . $e->getMessage());
        }
    }

    private function exportToPdf($filename)
    {
        $data = [
            'salesSummary' => $this->salesSummary,
            'profitSummary' => $this->profitSummary,
            'topProducts' => $this->topProducts,
            'topCustomers' => $this->topCustomers,
            'paymentMethods' => $this->paymentMethods,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];

        $pdf = Pdf::loadView('reports.sales-pdf', $data);

        $this->dispatch('trigger-download', [
            'url' => route('reports.sales.export-pdf', [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'format' => 'pdf'
            ]),
            'filename' => $filename . '.pdf'
        ]);

        $this->success('PDF export started!');
        $this->showExportModal = false;
    }

    private function exportToExcel($filename)
    {
        $this->dispatch('trigger-download', [
            'url' => route('reports.sales.export-excel', [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'format' => 'excel'
            ]),
            'filename' => $filename . '.xlsx'
        ]);

        $this->success('Excel export started!');
        $this->showExportModal = false;
    }

    public function render()
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->limit(100)->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('status', 'active')->orderBy('name')->limit(100)->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $dateRangeOptions = [
            ['id' => 'today', 'name' => 'Today'],
            ['id' => 'week', 'name' => 'This Week'],
            ['id' => 'month', 'name' => 'This Month'],
            ['id' => 'quarter', 'name' => 'This Quarter'],
            ['id' => 'year', 'name' => 'This Year'],
            ['id' => 'custom', 'name' => 'Custom Range'],
        ];

        $paymentMethodOptions = [
            ['id' => '', 'name' => 'All Payment Methods'],
            ['id' => 'cash', 'name' => 'Cash'],
            ['id' => 'card', 'name' => 'Credit/Debit Card'],
            ['id' => 'gcash', 'name' => 'GCash'],
            ['id' => 'bank_transfer', 'name' => 'Bank Transfer'],
        ];

        $statusOptions = [
            ['id' => 'completed', 'name' => 'Completed Only'],
            ['id' => '', 'name' => 'All Statuses'],
            ['id' => 'draft', 'name' => 'Draft'],
            ['id' => 'cancelled', 'name' => 'Cancelled'],
        ];

        $reportTypeOptions = [
            ['id' => 'overview', 'name' => 'Overview'],
            ['id' => 'products', 'name' => 'Product Performance'],
            ['id' => 'customers', 'name' => 'Customer Analysis'],
            ['id' => 'trends', 'name' => 'Sales Trends'],
            ['id' => 'users', 'name' => 'Staff Performance'],
        ];

        return view('livewire.reports.sales-reports', [
            'warehouses' => $warehouses,
            'customers' => $customers,
            'users' => $users,
            'products' => $products,
            'categories' => $categories,
            'dateRangeOptions' => $dateRangeOptions,
            'paymentMethodOptions' => $paymentMethodOptions,
            'statusOptions' => $statusOptions,
            'reportTypeOptions' => $reportTypeOptions,
        ])->layout('layouts.app', ['title' => 'Sales Reports']);
    }
}
