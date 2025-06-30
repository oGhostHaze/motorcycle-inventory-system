<?php

namespace App\Livewire\Reports;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class InventoryReports extends Component
{
    use WithPagination, Toast;

    // Report types
    public $reportType = 'stock_levels';

    // Filters
    public $warehouse = '';
    public $category = '';
    public $stockStatus = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Display options
    public $showChart = true;
    public $itemsPerPage = 25;
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    protected $queryString = [
        'reportType',
        'warehouse',
        'category',
        'stockStatus',
        'dateFrom',
        'dateTo',
        'sortBy',
        'sortDirection'
    ];

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
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

    public function updatedStockStatus()
    {
        $this->loadReportData();
    }

    private function loadReportData()
    {
        // This will trigger a re-render and update charts
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
            'categories' => Category::get(['id', 'name']),
            'stockStatuses' => [
                ['id' => '', 'name' => 'All Stock Levels'],
                ['id' => 'in_stock', 'name' => 'In Stock'],
                ['id' => 'low_stock', 'name' => 'Low Stock'],
                ['id' => 'out_of_stock', 'name' => 'Out of Stock'],
                ['id' => 'overstock', 'name' => 'Overstock'],
            ],
            'reportTypes' => [
                ['id' => 'stock_levels', 'name' => 'Stock Levels'],
                ['id' => 'valuation', 'name' => 'Inventory Valuation'],
                ['id' => 'movement', 'name' => 'Stock Movement'],
                ['id' => 'aging', 'name' => 'Inventory Aging'],
                ['id' => 'abc_analysis', 'name' => 'ABC Analysis'],
                ['id' => 'reorder', 'name' => 'Reorder Report'],
                ['id' => 'turnover', 'name' => 'Inventory Turnover'],
            ]
        ];

        return view('livewire.reports.inventory-reports', [
            'reportData' => $reportData,
            'summaryData' => $summaryData,
            'chartData' => $chartData,
            'filterOptions' => $filterOptions,
        ])->layout('layouts.app', ['title' => 'Inventory Reports']);
    }

    private function getReportData()
    {
        switch ($this->reportType) {
            case 'valuation':
                return $this->getValuationReport();
            case 'movement':
                return $this->getMovementReport();
            case 'aging':
                return $this->getAgingReport();
            case 'abc_analysis':
                return $this->getABCAnalysis();
            case 'reorder':
                return $this->getReorderReport();
            case 'turnover':
                return $this->getTurnoverReport();
            default:
                return $this->getStockLevelsReport();
        }
    }

    private function getStockLevelsReport()
    {
        $query = Inventory::with(['product.category', 'warehouse'])
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->select([
                'inventories.*',
                'products.name as product_name',
                'products.sku',
                'products.cost_price',
                'products.selling_price',
                'products.min_stock_level',
                'products.max_stock_level'
            ]);

        // Apply filters
        if ($this->warehouse) {
            $query->where('inventories.warehouse_id', $this->warehouse);
        }

        if ($this->category) {
            $query->where('products.category_id', $this->category);
        }

        if ($this->stockStatus) {
            switch ($this->stockStatus) {
                case 'in_stock':
                    $query->where('inventories.quantity_available', '>', 0);
                    break;
                case 'low_stock':
                    $query->whereColumn('inventories.quantity_available', '<=', 'products.min_stock_level')
                        ->where('inventories.quantity_available', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('inventories.quantity_available', '<=', 0);
                    break;
                case 'overstock':
                    $query->whereColumn('inventories.quantity_available', '>', 'products.max_stock_level')
                        ->whereNotNull('products.max_stock_level');
                    break;
            }
        }

        return $query->orderBy($this->sortBy == 'name' ? 'products.name' : $this->sortBy, $this->sortDirection)
            ->paginate($this->itemsPerPage);
    }

    private function getValuationReport()
    {
        $query = Inventory::with(['product.category', 'warehouse'])
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('
                inventories.*,
                products.name as product_name,
                products.sku,
                products.cost_price,
                products.selling_price,
                (inventories.quantity_on_hand * products.cost_price) as cost_value,
                (inventories.quantity_on_hand * products.selling_price) as retail_value,
                ((products.selling_price - products.cost_price) * inventories.quantity_on_hand) as potential_profit
            ');

        // Apply filters
        if ($this->warehouse) {
            $query->where('inventories.warehouse_id', $this->warehouse);
        }

        if ($this->category) {
            $query->where('products.category_id', $this->category);
        }

        return $query->orderBy('cost_value', 'desc')
            ->paginate($this->itemsPerPage);
    }

    private function getMovementReport()
    {
        $query = StockMovement::with(['product.category', 'warehouse', 'user'])
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);

        // Apply filters
        if ($this->warehouse) {
            $query->where('warehouse_id', $this->warehouse);
        }

        if ($this->category) {
            $query->whereHas('product', function ($q) {
                $q->where('category_id', $this->category);
            });
        }

        return $query->latest('created_at')
            ->paginate($this->itemsPerPage);
    }

    private function getAgingReport()
    {
        $query = Inventory::with(['product.category', 'warehouse'])
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->leftJoin('stock_movements', function ($join) {
                $join->on('inventories.product_id', '=', 'stock_movements.product_id')
                    ->on('inventories.warehouse_id', '=', 'stock_movements.warehouse_id')
                    ->whereIn('stock_movements.type', ['purchase', 'adjustment']);
            })
            ->selectRaw('
                inventories.*,
                products.name as product_name,
                products.sku,
                products.cost_price,
                MAX(stock_movements.created_at) as last_received,
                DATEDIFF(NOW(), MAX(stock_movements.created_at)) as days_since_received,
                (inventories.quantity_on_hand * products.cost_price) as holding_cost
            ')
            ->groupBy([
                'inventories.id',
                'inventories.product_id',
                'inventories.warehouse_id',
                'inventories.quantity_on_hand',
                'inventories.quantity_available',
                'inventories.quantity_reserved',
                'products.name',
                'products.sku',
                'products.cost_price'
            ]);

        // Apply filters
        if ($this->warehouse) {
            $query->where('inventories.warehouse_id', $this->warehouse);
        }

        if ($this->category) {
            $query->where('products.category_id', $this->category);
        }

        return $query->orderBy('days_since_received', 'desc')
            ->paginate($this->itemsPerPage);
    }

    private function getABCAnalysis()
    {
        // Calculate total sales value for each product in the date range
        $salesData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.completed_at', [$this->dateFrom, $this->dateTo])
            ->where('sales.status', 'completed')
            ->when($this->warehouse, fn($q) => $q->where('sales.warehouse_id', $this->warehouse))
            ->when($this->category, fn($q) => $q->where('products.category_id', $this->category))
            ->selectRaw('
                sale_items.product_id,
                products.name as product_name,
                products.sku,
                SUM(sale_items.unit_price * sale_items.quantity) as total_sales_value,
                SUM(sale_items.quantity) as total_quantity_sold
            ')
            ->groupBy('sale_items.product_id', 'products.name', 'products.sku')
            ->orderBy('total_sales_value', 'desc')
            ->get();

        // Calculate cumulative percentages and assign ABC categories
        $totalSalesValue = $salesData->sum('total_sales_value');
        $cumulativePercentage = 0;

        $salesData = $salesData->map(function ($item) use ($totalSalesValue, &$cumulativePercentage) {
            if ($totalSalesValue > 0) {
                $percentage = ($item->total_sales_value / $totalSalesValue) * 100;
                $cumulativePercentage += $percentage;

                if ($cumulativePercentage <= 80) {
                    $category = 'A';
                } elseif ($cumulativePercentage <= 95) {
                    $category = 'B';
                } else {
                    $category = 'C';
                }

                $item->percentage = $percentage;
                $item->cumulative_percentage = $cumulativePercentage;
                $item->abc_category = $category;
            } else {
                $item->percentage = 0;
                $item->cumulative_percentage = 0;
                $item->abc_category = 'C';
            }

            return $item;
        });

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $salesData->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), $this->itemsPerPage),
            $salesData->count(),
            $this->itemsPerPage,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            ['path' => request()->url()]
        );
    }

    private function getReorderReport()
    {
        $query = Inventory::with(['product.category', 'warehouse'])
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('
                inventories.*,
                products.name as product_name,
                products.sku,
                products.cost_price,
                products.min_stock_level,
                products.max_stock_level,
                GREATEST(0, COALESCE(products.reorder_quantity, products.min_stock_level, 10)) as reorder_quantity
            ')
            ->whereColumn('inventories.quantity_available', '<=', 'products.min_stock_level')
            ->whereNotNull('products.min_stock_level');

        // Apply filters
        if ($this->warehouse) {
            $query->where('inventories.warehouse_id', $this->warehouse);
        }

        if ($this->category) {
            $query->where('products.category_id', $this->category);
        }

        return $query->orderBy('inventories.quantity_available', 'asc')
            ->paginate($this->itemsPerPage);
    }

    private function getTurnoverReport()
    {
        // Calculate inventory turnover for each product
        $query = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('inventories', function ($join) {
                $join->on('products.id', '=', 'inventories.product_id');
                if ($this->warehouse) {
                    $join->where('inventories.warehouse_id', $this->warehouse);
                }
            })
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
                SUM(sale_items.quantity) as total_sold,
                AVG(inventories.quantity_on_hand) as avg_inventory,
                CASE
                    WHEN AVG(inventories.quantity_on_hand) > 0
                    THEN SUM(sale_items.quantity) / AVG(inventories.quantity_on_hand)
                    ELSE 0
                END as turnover_ratio,
                CASE
                    WHEN SUM(sale_items.quantity) > 0 AND AVG(inventories.quantity_on_hand) > 0
                    THEN 365 / (SUM(sale_items.quantity) / AVG(inventories.quantity_on_hand))
                    ELSE 365
                END as days_to_sell
            ')
            ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
            ->orderBy('turnover_ratio', 'desc');

        $results = $query->get();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $results->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), $this->itemsPerPage),
            $results->count(),
            $this->itemsPerPage,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            ['path' => request()->url()]
        );
    }

    private function getSummaryData()
    {
        $baseQuery = Inventory::with('product');

        if ($this->warehouse) {
            $baseQuery->where('warehouse_id', $this->warehouse);
        }

        $totalProducts = $baseQuery->count();
        $totalValue = $baseQuery->join('products', 'inventories.product_id', '=', 'products.id')
            ->sum(DB::raw('inventories.quantity_on_hand * products.cost_price'));
        $totalUnits = $baseQuery->sum('quantity_on_hand');

        $lowStockItems = Inventory::whereHas('product', function ($q) {
            $q->whereColumn('inventories.quantity_available', '<=', 'products.min_stock_level')
                ->whereNotNull('products.min_stock_level');
        })
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->count();

        $outOfStockItems = Inventory::where('quantity_available', '<=', 0)
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->count();

        return [
            'total_products' => $totalProducts,
            'total_value' => $totalValue,
            'total_units' => $totalUnits,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'avg_value_per_product' => $totalProducts > 0 ? $totalValue / $totalProducts : 0,
        ];
    }

    private function getChartData()
    {
        switch ($this->reportType) {
            case 'stock_levels':
                return $this->getStockLevelsChartData();
            case 'valuation':
                return $this->getValuationChartData();
            case 'abc_analysis':
                return $this->getABCChartData();
            default:
                return $this->getStockLevelsChartData();
        }
    }

    private function getStockLevelsChartData()
    {
        $stockStatus = Inventory::selectRaw('
                SUM(CASE WHEN quantity_available > 0 THEN 1 ELSE 0 END) as in_stock,
                SUM(CASE WHEN quantity_available <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE
                    WHEN quantity_available <= (SELECT min_stock_level FROM products WHERE products.id = inventories.product_id)
                    AND quantity_available > 0
                    AND (SELECT min_stock_level FROM products WHERE products.id = inventories.product_id) IS NOT NULL
                    THEN 1 ELSE 0
                END) as low_stock
            ')
            ->when($this->warehouse, fn($q) => $q->where('warehouse_id', $this->warehouse))
            ->first();

        return [
            'labels' => ['In Stock', 'Low Stock', 'Out of Stock'],
            'datasets' => [[
                'data' => [$stockStatus->in_stock, $stockStatus->low_stock, $stockStatus->out_of_stock],
                'backgroundColor' => ['#10B981', '#F59E0B', '#EF4444'],
            ]]
        ];
    }

    private function getValuationChartData()
    {
        $categories = Category::selectRaw('
                categories.name,
                SUM(inventories.quantity_on_hand * products.cost_price) as total_value
            ')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('inventories', 'products.id', '=', 'inventories.product_id')
            ->when($this->warehouse, fn($q) => $q->where('inventories.warehouse_id', $this->warehouse))
            ->groupBy('categories.id', 'categories.name')
            ->having('total_value', '>', 0)
            ->orderBy('total_value', 'desc')
            ->limit(10)
            ->get();

        return [
            'labels' => $categories->pluck('name')->toArray(),
            'datasets' => [[
                'label' => 'Inventory Value',
                'data' => $categories->pluck('total_value')->toArray(),
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

    private function getABCChartData()
    {
        // Get ABC analysis counts
        $abcData = $this->getABCAnalysis();
        $items = collect($abcData->items());
        $aCategoryCount = $items->where('abc_category', 'A')->count();
        $bCategoryCount = $items->where('abc_category', 'B')->count();
        $cCategoryCount = $items->where('abc_category', 'C')->count();

        return [
            'labels' => ['Category A (80%)', 'Category B (15%)', 'Category C (5%)'],
            'datasets' => [[
                'data' => [$aCategoryCount, $bCategoryCount, $cCategoryCount],
                'backgroundColor' => ['#10B981', '#F59E0B', '#EF4444'],
            ]]
        ];
    }

    public function exportToExcel()
    {
        $this->success('Excel export functionality would be implemented here');
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->reset(['warehouse', 'category', 'stockStatus']);
        $this->success('Filters cleared!');
    }
}
