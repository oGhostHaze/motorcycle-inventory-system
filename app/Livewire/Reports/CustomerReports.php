<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class CustomerReports extends Component
{
    use WithPagination, Toast;

    // Date filters
    public $dateFrom = '';
    public $dateTo = '';
    public $period = 'this_month';

    // Filters
    public $warehouse = '';
    public $customerGroup = '';
    public $customerStatus = 'active';

    // Report types
    public $reportType = 'customer_analysis';

    // Display options
    public $showChart = true;
    public $itemsPerPage = 25;
    public $sortBy = 'total_spent';
    public $sortDirection = 'desc';

    protected $queryString = [
        'dateFrom',
        'dateTo',
        'period',
        'warehouse',
        'customerGroup',
        'customerStatus',
        'reportType',
        'sortBy',
        'sortDirection'
    ];

    public function mount()
    {
        $this->setDateRange();
    }

    public function updatedPeriod()
    {
        $this->setDateRange();
        $this->loadReportData();
    }

    public function updatedReportType()
    {
        $this->loadReportData();
    }

    public function updatedWarehouse()
    {
        $this->loadReportData();
    }

    public function updatedCustomerGroup()
    {
        $this->loadReportData();
    }

    public function updatedCustomerStatus()
    {
        $this->loadReportData();
    }

    private function setDateRange()
    {
        $now = Carbon::now();

        switch ($this->period) {
            case 'this_week':
                $this->dateFrom = $now->startOfWeek()->format('Y-m-d');
                $this->dateTo = $now->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $this->dateFrom = $now->startOfMonth()->format('Y-m-d');
                $this->dateTo = $now->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                $this->dateFrom = $lastMonth->startOfMonth()->format('Y-m-d');
                $this->dateTo = $lastMonth->endOfMonth()->format('Y-m-d');
                break;
            case 'this_quarter':
                $this->dateFrom = $now->startOfQuarter()->format('Y-m-d');
                $this->dateTo = $now->endOfQuarter()->format('Y-m-d');
                break;
            case 'this_year':
                $this->dateFrom = $now->startOfYear()->format('Y-m-d');
                $this->dateTo = $now->endOfYear()->format('Y-m-d');
                break;
        }
    }

    private function loadReportData()
    {
        // This will trigger a re-render
        $this->dispatch('chartUpdated', [
            'chartData' => $this->getChartData(),
            'reportType' => $this->reportType
        ]);
    }

    public function render()
    {
        $reportData = $this->getReportData();
        $summaryData = $this->getSummaryData();
        $chartData = $this->getChartData();

        // Filter options
        $filterOptions = [
            'warehouses' => Warehouse::where('is_active', true)->get(['id', 'name']),
            'customerGroups' => [
                ['id' => '', 'name' => 'All Groups'],
                ['id' => 'retail', 'name' => 'Retail'],
                ['id' => 'wholesale', 'name' => 'Wholesale'],
                ['id' => 'vip', 'name' => 'VIP'],
            ],
            'customerStatuses' => [
                ['id' => '', 'name' => 'All Customers'],
                ['id' => 'active', 'name' => 'Active'],
                ['id' => 'inactive', 'name' => 'Inactive'],
            ],
            'periods' => [
                ['id' => 'this_week', 'name' => 'This Week'],
                ['id' => 'this_month', 'name' => 'This Month'],
                ['id' => 'last_month', 'name' => 'Last Month'],
                ['id' => 'this_quarter', 'name' => 'This Quarter'],
                ['id' => 'this_year', 'name' => 'This Year'],
                ['id' => 'custom', 'name' => 'Custom Range'],
            ],
            'reportTypes' => [
                ['id' => 'customer_analysis', 'name' => 'Customer Analysis'],
                ['id' => 'purchase_behavior', 'name' => 'Purchase Behavior'],
                ['id' => 'loyalty_analysis', 'name' => 'Loyalty Analysis'],
                ['id' => 'segmentation', 'name' => 'Customer Segmentation'],
                ['id' => 'lifetime_value', 'name' => 'Lifetime Value'],
                ['id' => 'product_preferences', 'name' => 'Product Preferences'],
            ]
        ];

        return view('livewire.reports.customer-reports', [
            'reportData' => $reportData,
            'summaryData' => $summaryData,
            'chartData' => $chartData,
            'filterOptions' => $filterOptions,
        ])->layout('layouts.app', ['title' => 'Customer Reports']);
    }

    private function getReportData()
    {
        switch ($this->reportType) {
            case 'purchase_behavior':
                return $this->getPurchaseBehavior();
            case 'loyalty_analysis':
                return $this->getLoyaltyAnalysis();
            case 'segmentation':
                return $this->getCustomerSegmentation();
            case 'lifetime_value':
                return $this->getLifetimeValue();
            case 'product_preferences':
                return $this->getProductPreferences();
            default:
                return $this->getCustomerAnalysis();
        }
    }

    private function getCustomerAnalysis()
    {
        $query = Sale::with('customer')
            ->selectRaw('
                customer_id,
                COUNT(*) as total_orders,
                SUM(total_amount) as total_spent,
                AVG(total_amount) as avg_order_value,
                MAX(completed_at) as last_purchase_date,
                MIN(completed_at) as first_purchase_date,
                SUM(CASE
                    WHEN sale_items.cost_price IS NOT NULL
                    THEN (sale_items.unit_price - sale_items.cost_price) * sale_items.quantity
                    ELSE 0
                END) as total_profit_generated
            ')
            ->leftJoin('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.completed_at', [$this->dateFrom, $this->dateTo])
            ->whereNotNull('sales.completed_at')
            ->where('sales.status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
            ->when($this->customerGroup, function ($q) {
                $q->whereHas('customer', fn($sq) => $sq->where('type', $this->customerGroup));
            })
            ->when($this->customerStatus, function ($q) {
                if ($this->customerStatus === 'active') {
                    $q->whereHas('customer', fn($sq) => $sq->where('is_active', true));
                } elseif ($this->customerStatus === 'inactive') {
                    $q->whereHas('customer', fn($sq) => $sq->where('is_active', false));
                }
            })
            ->groupBy('customer_id')
            ->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate($this->itemsPerPage);
    }

    private function getPurchaseBehavior()
    {
        return Sale::with('customer')
            ->selectRaw('
                customer_id,
                COUNT(*) as purchase_frequency,
                AVG(total_amount) as avg_purchase_amount,
                STDDEV(total_amount) as purchase_variance,
                AVG(HOUR(created_at)) as preferred_hour,
                CASE
                    WHEN COUNT(*) > 1 THEN
                        DATEDIFF(MAX(completed_at), MIN(completed_at)) / (COUNT(*) - 1)
                    ELSE 0
                END as avg_days_between_purchases
            ')
            ->whereBetween('completed_at', [$this->dateFrom, $this->dateTo])
            ->whereNotNull('completed_at')
            ->where('status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->groupBy('customer_id')
            ->having('purchase_frequency', '>', 1)
            ->orderBy('purchase_frequency', 'desc')
            ->paginate($this->itemsPerPage);
    }

    private function getLoyaltyAnalysis()
    {
        $loyaltyData = Customer::withCount(['sales' => function ($query) {
            $query->whereBetween('completed_at', [$this->dateFrom, $this->dateTo])
                ->where('status', 'completed');
            if ($this->warehouse) {
                $query->where('warehouse_id', $this->warehouse);
            }
        }])
            ->withSum(['sales' => function ($query) {
                $query->whereBetween('completed_at', [$this->dateFrom, $this->dateTo])
                    ->where('status', 'completed');
                if ($this->warehouse) {
                    $query->where('warehouse_id', $this->warehouse);
                }
            }], 'total_amount')
            ->when($this->customerGroup, fn($q) => $q->where('type', $this->customerGroup))
            ->when($this->customerStatus, function ($q) {
                if ($this->customerStatus === 'active') {
                    $q->where('is_active', true);
                } elseif ($this->customerStatus === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->get()
            ->map(function ($customer) {
                $totalSpent = $customer->sales_sum_total_amount ?? 0;
                $totalOrders = $customer->sales_count ?? 0;

                // Calculate loyalty score
                $loyaltyScore = 0;
                if ($totalOrders >= 10) $loyaltyScore += 30;
                elseif ($totalOrders >= 5) $loyaltyScore += 20;
                elseif ($totalOrders >= 2) $loyaltyScore += 10;

                if ($totalSpent >= 100000) $loyaltyScore += 30;
                elseif ($totalSpent >= 50000) $loyaltyScore += 20;
                elseif ($totalSpent >= 10000) $loyaltyScore += 10;

                $lastPurchase = $customer->sales()->latest('completed_at')->first();
                if ($lastPurchase && $lastPurchase->completed_at && $lastPurchase->completed_at->gte(Carbon::now()->subDays(30))) {
                    $loyaltyScore += 20;
                }

                $customer->loyalty_score = min($loyaltyScore, 100);
                $customer->total_spent = $totalSpent;
                $customer->total_orders = $totalOrders;

                return $customer;
            })
            ->sortByDesc('loyalty_score');

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $loyaltyData->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), $this->itemsPerPage),
            $loyaltyData->count(),
            $this->itemsPerPage,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            ['path' => request()->url()]
        );
    }

    private function getCustomerSegmentation()
    {
        $segmentationData = Sale::with('customer')
            ->selectRaw('
                customer_id,
                MAX(completed_at) as last_purchase,
                COUNT(*) as frequency,
                SUM(total_amount) as monetary_value,
                DATEDIFF(NOW(), MAX(completed_at)) as recency_days
            ')
            ->where('status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->whereNotNull('completed_at')
            ->groupBy('customer_id')
            ->get()
            ->map(function ($item) {
                // RFM scoring (1-5 scale)
                $recencyScore = $item->recency_days <= 30 ? 5 : ($item->recency_days <= 90 ? 4 : ($item->recency_days <= 180 ? 3 : ($item->recency_days <= 365 ? 2 : 1)));

                $frequencyScore = $item->frequency >= 10 ? 5 : ($item->frequency >= 5 ? 4 : ($item->frequency >= 3 ? 3 : ($item->frequency >= 2 ? 2 : 1)));

                $monetaryScore = $item->monetary_value >= 100000 ? 5 : ($item->monetary_value >= 50000 ? 4 : ($item->monetary_value >= 20000 ? 3 : ($item->monetary_value >= 5000 ? 2 : 1)));

                // Determine segment
                $avgScore = ($recencyScore + $frequencyScore + $monetaryScore) / 3;

                if ($avgScore >= 4.5) $segment = 'Champions';
                elseif ($avgScore >= 4) $segment = 'Loyal Customers';
                elseif ($avgScore >= 3.5) $segment = 'Potential Loyalists';
                elseif ($avgScore >= 3) $segment = 'New Customers';
                elseif ($avgScore >= 2.5) $segment = 'Promising';
                elseif ($avgScore >= 2) $segment = 'Need Attention';
                elseif ($avgScore >= 1.5) $segment = 'About to Sleep';
                else $segment = 'At Risk';

                $item->recency_score = $recencyScore;
                $item->frequency_score = $frequencyScore;
                $item->monetary_score = $monetaryScore;
                $item->segment = $segment;

                return $item;
            })
            ->sortByDesc('monetary_value');

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $segmentationData->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), $this->itemsPerPage),
            $segmentationData->count(),
            $this->itemsPerPage,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            ['path' => request()->url()]
        );
    }

    private function getLifetimeValue()
    {
        return Customer::selectRaw('
                customers.id,
                customers.name,
                customers.email,
                customers.phone,
                customers.type,
                customers.is_active,
                COUNT(sales.id) as total_orders,
                COALESCE(SUM(sales.total_amount), 0) as lifetime_value,
                COALESCE(AVG(sales.total_amount), 0) as avg_order_value,
                MAX(sales.completed_at) as last_purchase_date,
                MIN(sales.completed_at) as first_purchase_date,
                COALESCE(DATEDIFF(MAX(sales.completed_at), MIN(sales.completed_at)), 0) as customer_lifespan_days,
                CASE
                    WHEN COUNT(sales.id) > 1 AND DATEDIFF(MAX(sales.completed_at), MIN(sales.completed_at)) > 0
                    THEN COUNT(sales.id) / (DATEDIFF(MAX(sales.completed_at), MIN(sales.completed_at)) / 365.25)
                    ELSE 0
                END as purchase_frequency_per_year,
                CASE
                    WHEN COUNT(sales.id) > 1 AND DATEDIFF(MAX(sales.completed_at), MIN(sales.completed_at)) > 0
                    THEN (COUNT(sales.id) / (DATEDIFF(MAX(sales.completed_at), MIN(sales.completed_at)) / 365.25)) * AVG(sales.total_amount) * 3
                    ELSE COALESCE(SUM(sales.total_amount), 0)
                END as predicted_clv
            ')
            ->leftJoin('sales', function ($join) {
                $join->on('customers.id', '=', 'sales.customer_id')
                    ->where('sales.status', '=', 'completed')
                    ->whereNotNull('sales.completed_at');
            })
            ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
            ->when($this->customerGroup, fn($q) => $q->where('customers.type', $this->customerGroup))
            ->when($this->customerStatus, function ($q) {
                if ($this->customerStatus === 'active') {
                    $q->where('customers.is_active', true);
                } elseif ($this->customerStatus === 'inactive') {
                    $q->where('customers.is_active', false);
                }
            })
            ->groupBy('customers.id')
            ->orderBy('predicted_clv', 'desc')
            ->paginate($this->itemsPerPage);
    }

    private function getProductPreferences()
    {
        return SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereBetween('sales.completed_at', [$this->dateFrom, $this->dateTo])
            ->where('sales.status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
            ->when($this->customerGroup, fn($q) => $q->where('customers.type', $this->customerGroup))
            ->selectRaw('
                sales.customer_id,
                customers.name as customer_name,
                customers.email as customer_email,
                categories.name as preferred_category,
                COUNT(*) as category_purchases,
                SUM(sale_items.quantity) as total_quantity,
                SUM(sale_items.unit_price * sale_items.quantity) as total_spent_in_category,
                AVG(sale_items.unit_price) as avg_price_point
            ')
            ->groupBy('sales.customer_id', 'customers.name', 'customers.email', 'categories.id', 'categories.name')
            ->orderBy('total_spent_in_category', 'desc')
            ->paginate($this->itemsPerPage);
    }

    private function getSummaryData()
    {
        $baseQuery = Sale::whereBetween('completed_at', [$this->dateFrom, $this->dateTo])
            ->where('status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse));

        $totalCustomers = $baseQuery->distinct('customer_id')->count('customer_id');
        $totalRevenue = $baseQuery->sum('total_amount');
        $totalOrders = $baseQuery->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // New vs Returning customers
        $newCustomers = $baseQuery->whereHas('customer', function ($q) {
            $q->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
        })->distinct('customer_id')->count('customer_id');

        $returningCustomers = $totalCustomers - $newCustomers;

        // Top spending customer
        $topCustomer = $baseQuery->selectRaw('customer_id, SUM(total_amount) as total_spent')
            ->groupBy('customer_id')
            ->orderBy('total_spent', 'desc')
            ->first();

        $topCustomerSpent = $topCustomer ? $topCustomer->total_spent : 0;

        // Customer retention rate
        $periodDays = Carbon::parse($this->dateFrom)->diffInDays(Carbon::parse($this->dateTo)) + 1;
        $previousStart = Carbon::parse($this->dateFrom)->subDays($periodDays)->format('Y-m-d');
        $previousEnd = Carbon::parse($this->dateFrom)->subDay()->format('Y-m-d');

        $previousCustomers = Sale::whereBetween('completed_at', [$previousStart, $previousEnd])
            ->where('status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->distinct('customer_id')
            ->pluck('customer_id');

        $currentCustomers = $baseQuery->distinct('customer_id')->pluck('customer_id');
        $retainedCustomers = $previousCustomers->intersect($currentCustomers)->count();
        $retentionRate = $previousCustomers->count() > 0 ? ($retainedCustomers / $previousCustomers->count()) * 100 : 0;

        return [
            'total_customers' => $totalCustomers,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'avg_order_value' => $avgOrderValue,
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'top_customer_spent' => $topCustomerSpent,
            'retention_rate' => $retentionRate,
        ];
    }

    private function getChartData()
    {
        switch ($this->reportType) {
            case 'customer_analysis':
                return $this->getCustomerAnalysisChartData();
            case 'segmentation':
                return $this->getSegmentationChartData();
            case 'lifetime_value':
                return $this->getLifetimeValueChartData();
            default:
                return $this->getCustomerAnalysisChartData();
        }
    }

    private function getCustomerAnalysisChartData()
    {
        $cutoffDate = $this->dateFrom . ' 00:00:00';

        $data = DB::select("
            SELECT
                DATE(s.completed_at) as date,
                COUNT(DISTINCT CASE WHEN c.created_at >= ? THEN s.customer_id END) as new_customers,
                COUNT(DISTINCT CASE WHEN c.created_at < ? THEN s.customer_id END) as returning_customers
            FROM sales s
            INNER JOIN customers c ON s.customer_id = c.id
            WHERE s.completed_at BETWEEN ? AND ?
            AND s.status = 'completed'
            " . ($this->warehouse ? "AND s.warehouse_id = {$this->warehouse}" : "") . "
            GROUP BY DATE(s.completed_at)
            ORDER BY date
        ", [
            $cutoffDate,
            $cutoffDate,
            $this->dateFrom,
            $this->dateTo
        ]);

        $data = collect($data);

        $labels = $data->map(fn($item) => Carbon::parse($item->date)->format('M j'))->toArray();
        $newCustomersData = $data->pluck('new_customers')->toArray();
        $returningCustomersData = $data->pluck('returning_customers')->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'New Customers',
                    'data' => $newCustomersData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
                [
                    'label' => 'Returning Customers',
                    'data' => $returningCustomersData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ]
            ]
        ];
    }

    private function getSegmentationChartData()
    {
        $segmentData = $this->getCustomerSegmentation();
        $segmentCounts = collect($segmentData->items())->groupBy('segment')->map->count();

        return [
            'labels' => $segmentCounts->keys()->toArray(),
            'datasets' => [[
                'data' => $segmentCounts->values()->toArray(),
                'backgroundColor' => [
                    '#10B981',
                    '#3B82F6',
                    '#F59E0B',
                    '#EF4444',
                    '#8B5CF6',
                    '#F97316',
                    '#06B6D4',
                    '#84CC16'
                ],
            ]]
        ];
    }

    private function getLifetimeValueChartData()
    {
        $clvData = DB::select("
            SELECT
                clv_range,
                COUNT(*) as customer_count
            FROM (
                SELECT
                    c.id,
                    CASE
                        WHEN COALESCE(SUM(s.total_amount), 0) >= 100000 THEN '₱100K+'
                        WHEN COALESCE(SUM(s.total_amount), 0) >= 50000 THEN '₱50K-₱100K'
                        WHEN COALESCE(SUM(s.total_amount), 0) >= 20000 THEN '₱20K-₱50K'
                        WHEN COALESCE(SUM(s.total_amount), 0) >= 5000 THEN '₱5K-₱20K'
                        ELSE 'Under ₱5K'
                    END as clv_range
                FROM customers c
                LEFT JOIN sales s ON c.id = s.customer_id AND s.status = 'completed'
                " . ($this->warehouse ? "AND s.warehouse_id = {$this->warehouse}" : "") . "
                GROUP BY c.id
            ) customer_clv
            GROUP BY clv_range
            ORDER BY
                CASE clv_range
                    WHEN '₱100K+' THEN 1
                    WHEN '₱50K-₱100K' THEN 2
                    WHEN '₱20K-₱50K' THEN 3
                    WHEN '₱5K-₱20K' THEN 4
                    ELSE 5
                END
        ");

        $clvData = collect($clvData);

        return [
            'labels' => $clvData->pluck('clv_range')->toArray(),
            'datasets' => [[
                'label' => 'Customer Count',
                'data' => $clvData->pluck('customer_count')->toArray(),
                'backgroundColor' => [
                    '#10B981',
                    '#3B82F6',
                    '#F59E0B',
                    '#EF4444',
                    '#8B5CF6'
                ],
            ]]
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function exportToExcel()
    {
        $this->success('Excel export functionality would be implemented here');
    }

    public function clearFilters()
    {
        $this->reset(['warehouse', 'customerGroup', 'customerStatus']);
        $this->success('Filters cleared!');
    }

    public function refreshData()
    {
        $this->resetPage();
        $this->success('Customer reports data refreshed!');
    }
}
