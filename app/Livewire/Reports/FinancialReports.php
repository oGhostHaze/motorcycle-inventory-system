<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class FinancialReports extends Component
{
    use WithPagination, Toast;

    // Date filters
    public $dateFrom = '';
    public $dateTo = '';
    public $period = 'this_month';

    // Filters
    public $warehouse = '';
    public $category = '';

    // Report types
    public $reportType = 'profit_loss';

    // Display options
    public $showChart = true;
    public $itemsPerPage = 25;
    public $compareWithPrevious = false;

    protected $queryString = [
        'dateFrom',
        'dateTo',
        'period',
        'warehouse',
        'category',
        'reportType'
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

    public function updatedCategory()
    {
        $this->loadReportData();
    }

    private function setDateRange()
    {
        $now = Carbon::now();

        switch ($this->period) {
            case 'today':
                $this->dateFrom = $now->format('Y-m-d');
                $this->dateTo = $now->format('Y-m-d');
                break;
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
        $comparisonData = $this->compareWithPrevious ? $this->getComparisonData() : null;

        // Filter options
        $filterOptions = [
            'warehouses' => Warehouse::where('is_active', true)->get(['id', 'name']),
            'categories' => Category::get(['id', 'name']),
            'periods' => [
                ['id' => 'today', 'name' => 'Today'],
                ['id' => 'this_week', 'name' => 'This Week'],
                ['id' => 'this_month', 'name' => 'This Month'],
                ['id' => 'last_month', 'name' => 'Last Month'],
                ['id' => 'this_quarter', 'name' => 'This Quarter'],
                ['id' => 'this_year', 'name' => 'This Year'],
                ['id' => 'custom', 'name' => 'Custom Range'],
            ],
            'reportTypes' => [
                ['id' => 'profit_loss', 'name' => 'Profit & Loss'],
                ['id' => 'cost_analysis', 'name' => 'Cost Analysis'],
                ['id' => 'margin_analysis', 'name' => 'Margin Analysis'],
                ['id' => 'expense_breakdown', 'name' => 'Expense Breakdown'],
                ['id' => 'cash_flow', 'name' => 'Cash Flow'],
                ['id' => 'roi_analysis', 'name' => 'ROI Analysis'],
            ]
        ];

        return view('livewire.reports.financial-reports', [
            'reportData' => $reportData,
            'summaryData' => $summaryData,
            'chartData' => $chartData,
            'comparisonData' => $comparisonData,
            'filterOptions' => $filterOptions,
        ])->layout('layouts.app', ['title' => 'Financial Reports']);
    }

    private function getReportData()
    {
        switch ($this->reportType) {
            case 'cost_analysis':
                return $this->getCostAnalysis();
            case 'margin_analysis':
                return $this->getMarginAnalysis();
            case 'expense_breakdown':
                return $this->getExpenseBreakdown();
            case 'cash_flow':
                return $this->getCashFlow();
            case 'roi_analysis':
                return $this->getROIAnalysis();
            default:
                return $this->getProfitLoss();
        }
    }

    private function getProfitLoss()
    {
        // Daily P&L for the period
        return Sale::selectRaw('
                DATE(completed_at) as date,
                SUM(total_amount) as revenue,
                (
                    SELECT COALESCE(SUM(products.cost_price * sale_items.quantity), 0)
                    FROM sale_items
                    JOIN products ON sale_items.product_id = products.id
                    WHERE sale_items.sale_id IN (
                        SELECT id FROM sales s2 WHERE DATE(s2.completed_at) = DATE(sales.completed_at)
                        AND s2.status = "completed"
                        ' . ($this->warehouse ? ' AND s2.warehouse_id = ' . $this->warehouse : '') . '
                    )
                ) as cogs,
                (
                    SELECT COALESCE(SUM((sale_items.unit_price - COALESCE(sale_items.cost_price, products.cost_price)) * sale_items.quantity), 0)
                    FROM sale_items
                    JOIN products ON sale_items.product_id = products.id
                    WHERE sale_items.sale_id IN (
                        SELECT id FROM sales s2 WHERE DATE(s2.completed_at) = DATE(sales.completed_at)
                        AND s2.status = "completed"
                        ' . ($this->warehouse ? ' AND s2.warehouse_id = ' . $this->warehouse : '') . '
                    )
                ) as gross_profit,
                COUNT(*) as transactions
            ')
            ->whereBetween('completed_at', [$this->dateFrom, $this->dateTo])
            ->where('status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->paginate($this->itemsPerPage);
    }

    private function getCostAnalysis()
    {
        // Cost breakdown by product category
        return SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.completed_at', [$this->dateFrom, $this->dateTo])
            ->where('sales.status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
            ->when($this->category, fn($q) => $q->where('products.category_id', $this->category))
            ->selectRaw('
                categories.name as category_name,
                SUM(sale_items.quantity) as total_quantity,
                SUM(COALESCE(sale_items.cost_price, products.cost_price) * sale_items.quantity) as total_cost,
                SUM(sale_items.unit_price * sale_items.quantity) as total_revenue,
                SUM((sale_items.unit_price - COALESCE(sale_items.cost_price, products.cost_price)) * sale_items.quantity) as total_profit,
                AVG(COALESCE(sale_items.cost_price, products.cost_price)) as avg_unit_cost,
                AVG(sale_items.unit_price) as avg_selling_price
            ')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_cost', 'desc')
            ->paginate($this->itemsPerPage);
    }

    private function getMarginAnalysis()
    {
        // Margin analysis by product
        return SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.completed_at', [$this->dateFrom, $this->dateTo])
            ->where('sales.status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
            ->when($this->category, fn($q) => $q->where('products.category_id', $this->category))
            ->selectRaw('
                products.id as product_id,
                products.name as product_name,
                products.sku,
                categories.name as category_name,
                SUM(sale_items.quantity) as total_quantity,
                SUM(COALESCE(sale_items.cost_price, products.cost_price) * sale_items.quantity) as total_cost,
                SUM(sale_items.unit_price * sale_items.quantity) as total_revenue,
                SUM((sale_items.unit_price - COALESCE(sale_items.cost_price, products.cost_price)) * sale_items.quantity) as total_profit,
                AVG(COALESCE(sale_items.cost_price, products.cost_price)) as avg_unit_cost,
                AVG(sale_items.unit_price) as avg_selling_price,
                CASE
                    WHEN SUM(sale_items.unit_price * sale_items.quantity) > 0
                    THEN (SUM((sale_items.unit_price - COALESCE(sale_items.cost_price, products.cost_price)) * sale_items.quantity) / SUM(sale_items.unit_price * sale_items.quantity)) * 100
                    ELSE 0
                END as profit_margin_percentage
            ')
            ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
            ->orderBy('profit_margin_percentage', 'desc')
            ->paginate($this->itemsPerPage);
    }

    private function getExpenseBreakdown()
    {
        // Purchase orders as expenses
        return PurchaseOrder::with(['supplier', 'warehouse'])
            ->whereBetween('order_date', [$this->dateFrom, $this->dateTo])
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->selectRaw('
                purchase_orders.*,
                CASE
                    WHEN status = "completed" THEN total_amount
                    ELSE 0
                END as actual_expense
            ')
            ->orderBy('order_date', 'desc')
            ->paginate($this->itemsPerPage);
    }

    private function getCashFlow()
    {
        // Weekly cash flow analysis
        $cashFlowData = [];

        // Get sales (cash inflow)
        $sales = Sale::selectRaw('
                YEARWEEK(completed_at) as week,
                MIN(completed_at) as week_start,
                SUM(total_amount) as cash_in
            ')
            ->whereBetween('completed_at', [$this->dateFrom, $this->dateTo])
            ->where('status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->groupBy('week')
            ->get()
            ->keyBy('week');

        // Get purchases (cash outflow)
        $purchases = PurchaseOrder::selectRaw('
                YEARWEEK(order_date) as week,
                MIN(order_date) as week_start,
                SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as cash_out
            ')
            ->whereBetween('order_date', [$this->dateFrom, $this->dateTo])
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->groupBy('week')
            ->get()
            ->keyBy('week');

        // Combine data
        $allWeeks = $sales->keys()->merge($purchases->keys())->unique()->sort();

        foreach ($allWeeks as $week) {
            $sale = $sales->get($week);
            $purchase = $purchases->get($week);

            $cashIn = $sale ? $sale->cash_in : 0;
            $cashOut = $purchase ? $purchase->cash_out : 0;
            $netFlow = $cashIn - $cashOut;

            $cashFlowData[] = (object) [
                'week' => $week,
                'week_start' => $sale->week_start ?? $purchase->week_start,
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'net_flow' => $netFlow,
            ];
        }

        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect($cashFlowData)->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), $this->itemsPerPage),
            count($cashFlowData),
            $this->itemsPerPage,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            ['path' => request()->url()]
        );
    }

    private function getROIAnalysis()
    {
        // ROI analysis by product category
        return SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.completed_at', [$this->dateFrom, $this->dateTo])
            ->where('sales.status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
            ->selectRaw('
                categories.name as category_name,
                SUM(COALESCE(sale_items.cost_price, products.cost_price) * sale_items.quantity) as total_investment,
                SUM(sale_items.unit_price * sale_items.quantity) as total_return,
                SUM((sale_items.unit_price - COALESCE(sale_items.cost_price, products.cost_price)) * sale_items.quantity) as total_profit,
                CASE
                    WHEN SUM(COALESCE(sale_items.cost_price, products.cost_price) * sale_items.quantity) > 0
                    THEN (SUM((sale_items.unit_price - COALESCE(sale_items.cost_price, products.cost_price)) * sale_items.quantity) / SUM(COALESCE(sale_items.cost_price, products.cost_price) * sale_items.quantity)) * 100
                    ELSE 0
                END as roi_percentage,
                COUNT(DISTINCT sales.id) as transaction_count
            ')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('roi_percentage', 'desc')
            ->paginate($this->itemsPerPage);
    }

    private function getSummaryData()
    {
        // Current period summary
        $currentSales = Sale::whereBetween('completed_at', [$this->dateFrom, $this->dateTo])
            ->where('status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->selectRaw('
                SUM(total_amount) as total_revenue,
                COUNT(*) as total_transactions,
                AVG(total_amount) as avg_transaction_value
            ')
            ->first();

        // Calculate COGS and profit from sale_items
        $profitData = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.completed_at', [$this->dateFrom, $this->dateTo])
            ->where('sales.status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
            ->selectRaw('
                SUM(COALESCE(sale_items.cost_price, products.cost_price) * sale_items.quantity) as total_cogs,
                SUM((sale_items.unit_price - COALESCE(sale_items.cost_price, products.cost_price)) * sale_items.quantity) as total_profit
            ')
            ->first();

        $currentPurchases = PurchaseOrder::whereBetween('order_date', [$this->dateFrom, $this->dateTo])
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->where('status', 'completed')
            ->sum('total_amount');

        // Calculate inventory value
        $inventoryValue = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->when($this->warehouse, fn($q) => $q->where('inventories.warehouse_id', $this->warehouse))
            ->sum(DB::raw('inventories.quantity_on_hand * products.cost_price'));

        // Profit margin
        $profitMargin = $currentSales->total_revenue > 0
            ? (($profitData->total_profit ?? 0) / $currentSales->total_revenue) * 100
            : 0;

        // ROI calculation
        $roi = $currentPurchases > 0
            ? (($profitData->total_profit ?? 0) / $currentPurchases) * 100
            : 0;

        return [
            'total_revenue' => $currentSales->total_revenue ?? 0,
            'total_cogs' => $profitData->total_cogs ?? 0,
            'total_profit' => $profitData->total_profit ?? 0,
            'total_expenses' => $currentPurchases,
            'net_income' => ($profitData->total_profit ?? 0) - $currentPurchases,
            'profit_margin' => $profitMargin,
            'roi' => $roi,
            'inventory_value' => $inventoryValue,
            'total_transactions' => $currentSales->total_transactions ?? 0,
            'avg_transaction_value' => $currentSales->avg_transaction_value ?? 0,
        ];
    }

    private function getChartData()
    {
        switch ($this->reportType) {
            case 'profit_loss':
                return $this->getProfitLossChartData();
            case 'cost_analysis':
                return $this->getCostAnalysisChartData();
            case 'margin_analysis':
                return $this->getMarginAnalysisChartData();
            case 'cash_flow':
                return $this->getCashFlowChartData();
            default:
                return $this->getProfitLossChartData();
        }
    }

    private function getProfitLossChartData()
    {
        $data = Sale::selectRaw('
                DATE(completed_at) as date,
                SUM(total_amount) as revenue
            ')
            ->whereBetween('completed_at', [$this->dateFrom, $this->dateTo])
            ->where('status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Calculate COGS and profit for each date
        $enrichedData = $data->map(function ($item) {
            $cogsAndProfit = DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereDate('sales.completed_at', $item->date)
                ->where('sales.status', 'completed')
                ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
                ->selectRaw('
                    SUM(COALESCE(sale_items.cost_price, products.cost_price) * sale_items.quantity) as cogs,
                    SUM((sale_items.unit_price - COALESCE(sale_items.cost_price, products.cost_price)) * sale_items.quantity) as profit
                ')
                ->first();

            $item->cogs = $cogsAndProfit->cogs ?? 0;
            $item->profit = $cogsAndProfit->profit ?? 0;
            return $item;
        });

        $labels = $enrichedData->map(fn($item) => Carbon::parse($item->date)->format('M j'))->toArray();
        $revenueData = $enrichedData->pluck('revenue')->toArray();
        $cogsData = $enrichedData->pluck('cogs')->toArray();
        $profitData = $enrichedData->pluck('profit')->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'COGS',
                    'data' => $cogsData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
                [
                    'label' => 'Profit',
                    'data' => $profitData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ]
            ]
        ];
    }

    private function getCostAnalysisChartData()
    {
        $data = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.completed_at', [$this->dateFrom, $this->dateTo])
            ->where('sales.status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
            ->selectRaw('
                categories.name as category_name,
                SUM(COALESCE(sale_items.cost_price, products.cost_price) * sale_items.quantity) as total_cost
            ')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_cost', 'desc')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('category_name')->toArray(),
            'datasets' => [[
                'label' => 'Cost Amount',
                'data' => $data->pluck('total_cost')->toArray(),
                'backgroundColor' => [
                    '#3B82F6',
                    '#10B981',
                    '#F59E0B',
                    '#EF4444',
                    '#8B5CF6',
                    '#F97316',
                    '#06B6D4',
                    '#84CC16',
                    '#EC4899',
                    '#6B7280'
                ],
            ]]
        ];
    }

    private function getMarginAnalysisChartData()
    {
        $data = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.completed_at', [$this->dateFrom, $this->dateTo])
            ->where('sales.status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
            ->selectRaw('
                categories.name as category_name,
                CASE
                    WHEN SUM(sale_items.unit_price * sale_items.quantity) > 0
                    THEN (SUM((sale_items.unit_price - COALESCE(sale_items.cost_price, products.cost_price)) * sale_items.quantity) / SUM(sale_items.unit_price * sale_items.quantity)) * 100
                    ELSE 0
                END as profit_margin
            ')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('profit_margin', 'desc')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('category_name')->toArray(),
            'datasets' => [[
                'label' => 'Profit Margin (%)',
                'data' => $data->pluck('profit_margin')->toArray(),
                'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                'borderColor' => 'rgb(16, 185, 129)',
            ]]
        ];
    }

    private function getCashFlowChartData()
    {
        $cashFlowData = $this->getCashFlow();

        $labels = collect($cashFlowData->items())->map(fn($item) => Carbon::parse($item->week_start)->format('M j'))->toArray();
        $cashInData = collect($cashFlowData->items())->pluck('cash_in')->toArray();
        $cashOutData = collect($cashFlowData->items())->pluck('cash_out')->toArray();
        $netFlowData = collect($cashFlowData->items())->pluck('net_flow')->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Cash In',
                    'data' => $cashInData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
                [
                    'label' => 'Cash Out',
                    'data' => $cashOutData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
                [
                    'label' => 'Net Flow',
                    'data' => $netFlowData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ]
            ]
        ];
    }

    private function getComparisonData()
    {
        // Calculate previous period dates
        $periodDays = Carbon::parse($this->dateFrom)->diffInDays(Carbon::parse($this->dateTo)) + 1;
        $previousStart = Carbon::parse($this->dateFrom)->subDays($periodDays)->format('Y-m-d');
        $previousEnd = Carbon::parse($this->dateFrom)->subDay()->format('Y-m-d');

        // Get previous period data
        $previousSales = Sale::whereBetween('completed_at', [$previousStart, $previousEnd])
            ->where('status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->selectRaw('
                SUM(total_amount) as total_revenue,
                COUNT(*) as total_transactions
            ')
            ->first();

        $previousProfit = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.completed_at', [$previousStart, $previousEnd])
            ->where('sales.status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
            ->selectRaw('
                SUM((sale_items.unit_price - COALESCE(sale_items.cost_price, products.cost_price)) * sale_items.quantity) as total_profit
            ')
            ->first();

        $previousPurchases = PurchaseOrder::whereBetween('order_date', [$previousStart, $previousEnd])
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->where('status', 'completed')
            ->sum('total_amount');

        return [
            'previous_revenue' => $previousSales->total_revenue ?? 0,
            'previous_profit' => $previousProfit->total_profit ?? 0,
            'previous_transactions' => $previousSales->total_transactions ?? 0,
            'previous_expenses' => $previousPurchases,
        ];
    }

    public function exportToExcel()
    {
        $this->success('Excel export functionality would be implemented here');
    }

    public function clearFilters()
    {
        $this->reset(['warehouse', 'category']);
        $this->success('Filters cleared!');
    }
}
