<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inventory Reports</h1>
            <p class="text-gray-600 dark:text-gray-400">Monitor stock levels, valuation, and inventory performance</p>
        </div>
        <div class="flex gap-2">
            <x-mary-button label="Export Excel" icon="o-document-arrow-down" wire:click="exportToExcel"
                class="btn-outline" />
            <x-mary-button label="Clear Filters" icon="o-x-mark" wire:click="clearFilters" class="btn-ghost" />
        </div>
    </div>

    {{-- Filters Section --}}
    <x-mary-card title="Filters" class="mb-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            {{-- Report Type --}}
            <x-mary-select label="Report Type" wire:model.live="reportType" :options="$filterOptions['reportTypes']" />

            {{-- Warehouse Filter --}}
            <x-mary-select label="Warehouse" wire:model.live="warehouse" :options="$filterOptions['warehouses']"
                placeholder="All Warehouses" />

            {{-- Category Filter --}}
            <x-mary-select label="Category" wire:model.live="category" :options="$filterOptions['categories']" placeholder="All Categories" />

            {{-- Brand Filter --}}
            <x-mary-select label="Brand" wire:model.live="brand" :options="$filterOptions['brands']" placeholder="All Brands" />

            {{-- Stock Status Filter --}}
            @if (in_array($reportType, ['stock_levels']))
                <x-mary-select label="Stock Status" wire:model.live="stockStatus" :options="$filterOptions['stockStatuses']" />
            @endif

            {{-- Date Range for Movement/Aging/ABC/Turnover reports --}}
            @if (in_array($reportType, ['movement', 'aging', 'abc_analysis', 'turnover']))
                <x-mary-datetime label="Date From" wire:model.live="dateFrom" type="date" />
                <x-mary-datetime label="Date To" wire:model.live="dateTo" type="date" />
            @endif
        </div>
    </x-mary-card>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
        <x-mary-stat title="Total Products" value="{{ number_format($summaryData['total_products']) }}" icon="o-cube"
            color="text-primary" />

        <x-mary-stat title="Total Value" value="₱{{ number_format($summaryData['total_value'], 2) }}" icon="o-banknotes"
            color="text-success" />

        <x-mary-stat title="Total Units" value="{{ number_format($summaryData['total_units']) }}" icon="o-squares-plus"
            color="text-info" />

        <x-mary-stat title="Low Stock Items" value="{{ number_format($summaryData['low_stock_items']) }}"
            icon="o-exclamation-triangle" color="text-warning" />

        <x-mary-stat title="Out of Stock" value="{{ number_format($summaryData['out_of_stock_items']) }}"
            icon="o-x-circle" color="text-error" />

        <x-mary-stat title="Avg Value/Product" value="₱{{ number_format($summaryData['avg_value_per_product'], 2) }}"
            icon="o-calculator" color="text-secondary" />
    </div>

    {{-- Chart Section --}}
    @if ($showChart && in_array($reportType, ['stock_levels', 'valuation', 'abc_analysis']))
        <x-mary-card title="Visual Analysis" class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium">
                    @if ($reportType === 'stock_levels')
                        Stock Status Distribution
                    @elseif($reportType === 'valuation')
                        Inventory Value by Category
                    @elseif($reportType === 'abc_analysis')
                        ABC Category Distribution
                    @endif
                </h3>
                <x-mary-button icon="{{ $showChart ? 'o-eye-slash' : 'o-eye' }}" wire:click="$toggle('showChart')"
                    class="btn-ghost btn-sm" />
            </div>

            <div class="h-96">
                <canvas id="inventoryChart"></canvas>
            </div>
        </x-mary-card>
    @endif

    {{-- Report Data Section --}}
    <x-mary-card>
        <x-slot:title>
            {{ collect($filterOptions['reportTypes'])->firstWhere('value', $reportType)['label'] ?? 'Report Data' }}
        </x-slot:title>

        @if ($reportType === 'stock_levels')
            {{-- Stock Levels Table --}}
            <x-mary-table :headers="[
                ['key' => 'product_name', 'label' => 'Product', 'sortable' => true],
                ['key' => 'sku', 'label' => 'SKU'],
                ['key' => 'warehouse', 'label' => 'Warehouse'],
                ['key' => 'quantity_on_hand', 'label' => 'On Hand'],
                ['key' => 'quantity_available', 'label' => 'Available'],
                ['key' => 'quantity_reserved', 'label' => 'Reserved'],
                ['key' => 'min_stock_level', 'label' => 'Min Level'],
                ['key' => 'status', 'label' => 'Status'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_product_name', $row)
                    <div>
                        <div class="font-medium">{{ $row->product_name }}</div>
                        <div class="text-sm text-gray-500">{{ $row->product->category?->name }}</div>
                    </div>
                @endscope

                @scope('cell_warehouse', $row)
                    {{ $row->warehouse->name }}
                @endscope

                @scope('cell_quantity_on_hand', $row)
                    <x-mary-badge value="{{ number_format($row->quantity_on_hand) }}" class="badge-info" />
                @endscope

                @scope('cell_quantity_available', $row)
                    <x-mary-badge value="{{ number_format($row->quantity_available) }}"
                        class="{{ $row->quantity_available <= 0 ? 'badge-error' : ($row->quantity_available <= ($row->min_stock_level ?? 0) ? 'badge-warning' : 'badge-success') }}" />
                @endscope

                @scope('cell_quantity_reserved', $row)
                    {{ number_format($row->quantity_reserved) }}
                @endscope

                @scope('cell_min_stock_level', $row)
                    {{ $row->min_stock_level ? number_format($row->min_stock_level) : '-' }}
                @endscope

                @scope('cell_status', $row)
                    @php
                        $status = 'In Stock';
                        $class = 'badge-success';

                        if ($row->quantity_available <= 0) {
                            $status = 'Out of Stock';
                            $class = 'badge-error';
                        } elseif ($row->min_stock_level && $row->quantity_available <= $row->min_stock_level) {
                            $status = 'Low Stock';
                            $class = 'badge-warning';
                        } elseif ($row->max_stock_level && $row->quantity_available > $row->max_stock_level) {
                            $status = 'Overstock';
                            $class = 'badge-info';
                        }
                    @endphp
                    <x-mary-badge value="{{ $status }}" class="{{ $class }}" />
                @endscope
            </x-mary-table>
        @elseif($reportType === 'valuation')
            {{-- Valuation Table --}}
            <x-mary-table :headers="[
                ['key' => 'product_name', 'label' => 'Product'],
                ['key' => 'sku', 'label' => 'SKU'],
                ['key' => 'warehouse', 'label' => 'Warehouse'],
                ['key' => 'quantity_on_hand', 'label' => 'Quantity'],
                ['key' => 'cost_price', 'label' => 'Cost Price'],
                ['key' => 'cost_value', 'label' => 'Cost Value'],
                ['key' => 'retail_value', 'label' => 'Retail Value'],
                ['key' => 'potential_profit', 'label' => 'Potential Profit'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_product_name', $row)
                    <div>
                        <div class="font-medium">{{ $row->product_name }}</div>
                        <div class="text-sm text-gray-500">{{ $row->product->category?->name }}</div>
                    </div>
                @endscope

                @scope('cell_warehouse', $row)
                    {{ $row->warehouse->name }}
                @endscope

                @scope('cell_quantity_on_hand', $row)
                    {{ number_format($row->quantity_on_hand) }}
                @endscope

                @scope('cell_cost_price', $row)
                    ₱{{ number_format($row->cost_price, 2) }}
                @endscope

                @scope('cell_cost_value', $row)
                    <span class="font-medium text-primary">₱{{ number_format($row->cost_value, 2) }}</span>
                @endscope

                @scope('cell_retail_value', $row)
                    <span class="font-medium text-success">₱{{ number_format($row->retail_value, 2) }}</span>
                @endscope

                @scope('cell_potential_profit', $row)
                    <span class="font-medium text-warning">₱{{ number_format($row->potential_profit, 2) }}</span>
                @endscope
            </x-mary-table>
        @elseif($reportType === 'movement')
            {{-- Stock Movement Table --}}
            <x-mary-table :headers="[
                ['key' => 'movement_date', 'label' => 'Date'],
                ['key' => 'product', 'label' => 'Product'],
                ['key' => 'warehouse', 'label' => 'Warehouse'],
                ['key' => 'movement_type', 'label' => 'Type'],
                ['key' => 'quantity', 'label' => 'Quantity'],
                ['key' => 'reference', 'label' => 'Reference'],
                ['key' => 'notes', 'label' => 'Notes'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_movement_date', $row)
                    {{ $row->created_at->format('M j, Y H:i') }}
                @endscope

                @scope('cell_product', $row)
                    <div>
                        <div class="font-medium">{{ $row->product->name }}</div>
                        <div class="text-sm text-gray-500">{{ $row->product->sku }}</div>
                    </div>
                @endscope

                @scope('cell_warehouse', $row)
                    {{ $row->warehouse->name }}
                @endscope

                @scope('cell_movement_type', $row)
                    <x-mary-badge value="{{ ucfirst($row->type) }}"
                        class="{{ in_array($row->type, ['purchase', 'adjustment', 'transfer']) ? 'badge-success' : 'badge-error' }}" />
                @endscope

                @scope('cell_quantity', $row)
                    <span class="{{ $row->quantity_changed > 0 ? 'text-success' : 'text-error' }}">
                        {{ $row->quantity_changed > 0 ? '+' : '' }}{{ number_format($row->quantity_changed) }}
                    </span>
                @endscope

                @scope('cell_reference', $row)
                    {{ $row->reference_type ?? 'Manual' }}: {{ $row->reference_id ?? '-' }}
                @endscope

                @scope('cell_notes', $row)
                    {{ $row->notes ?: '-' }}
                @endscope
            </x-mary-table>
        @elseif($reportType === 'aging')
            {{-- Aging Report Table --}}
            <x-mary-table :headers="[
                ['key' => 'product_name', 'label' => 'Product'],
                ['key' => 'sku', 'label' => 'SKU'],
                ['key' => 'warehouse', 'label' => 'Warehouse'],
                ['key' => 'quantity_on_hand', 'label' => 'Quantity'],
                ['key' => 'last_received', 'label' => 'Last Received'],
                ['key' => 'days_since_received', 'label' => 'Days Old'],
                ['key' => 'holding_cost', 'label' => 'Holding Cost'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_product_name', $row)
                    <div>
                        <div class="font-medium">{{ $row->product_name }}</div>
                        <div class="text-sm text-gray-500">{{ $row->product->category?->name }}</div>
                    </div>
                @endscope

                @scope('cell_warehouse', $row)
                    {{ $row->warehouse->name }}
                @endscope

                @scope('cell_quantity_on_hand', $row)
                    {{ number_format($row->quantity_on_hand) }}
                @endscope

                @scope('cell_last_received', $row)
                    {{ $row->last_received ? Carbon\Carbon::parse($row->last_received)->format('M j, Y') : 'Never' }}
                @endscope

                @scope('cell_days_since_received', $row)
                    @php
                        $days = $row->days_since_received ?? 0;
                        $class = $days > 365 ? 'badge-error' : ($days > 180 ? 'badge-warning' : 'badge-success');
                    @endphp
                    <x-mary-badge value="{{ $days }} days" class="{{ $class }}" />
                @endscope

                @scope('cell_holding_cost', $row)
                    <span class="font-medium">₱{{ number_format($row->holding_cost, 2) }}</span>
                @endscope
            </x-mary-table>
        @elseif($reportType === 'abc_analysis')
            {{-- ABC Analysis Table --}}
            <x-mary-table :headers="[
                ['key' => 'product_name', 'label' => 'Product'],
                ['key' => 'sku', 'label' => 'SKU'],
                ['key' => 'total_sales_value', 'label' => 'Sales Value'],
                ['key' => 'total_quantity_sold', 'label' => 'Qty Sold'],
                ['key' => 'percentage', 'label' => 'Contribution %'],
                ['key' => 'cumulative_percentage', 'label' => 'Cumulative %'],
                ['key' => 'abc_category', 'label' => 'ABC Category'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_total_sales_value', $row)
                    <span class="font-medium text-primary">₱{{ number_format($row->total_sales_value, 2) }}</span>
                @endscope

                @scope('cell_total_quantity_sold', $row)
                    {{ number_format($row->total_quantity_sold) }}
                @endscope

                @scope('cell_percentage', $row)
                    {{ number_format($row->percentage, 2) }}%
                @endscope

                @scope('cell_cumulative_percentage', $row)
                    {{ number_format($row->cumulative_percentage, 2) }}%
                @endscope

                @scope('cell_abc_category', $row)
                    <x-mary-badge value="Category {{ $row->abc_category }}"
                        class="{{ $row->abc_category === 'A' ? 'badge-success' : ($row->abc_category === 'B' ? 'badge-warning' : 'badge-error') }}" />
                @endscope
            </x-mary-table>
        @elseif($reportType === 'reorder')
            {{-- Reorder Report Table --}}
            <x-mary-table :headers="[
                ['key' => 'product_name', 'label' => 'Product'],
                ['key' => 'sku', 'label' => 'SKU'],
                ['key' => 'warehouse', 'label' => 'Warehouse'],
                ['key' => 'quantity_available', 'label' => 'Current Stock'],
                ['key' => 'min_stock_level', 'label' => 'Min Level'],
                ['key' => 'reorder_quantity', 'label' => 'Suggested Reorder'],
                ['key' => 'cost_estimate', 'label' => 'Cost Estimate'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_product_name', $row)
                    <div>
                        <div class="font-medium">{{ $row->product_name }}</div>
                        <div class="text-sm text-gray-500">{{ $row->product->category?->name }}</div>
                    </div>
                @endscope

                @scope('cell_warehouse', $row)
                    {{ $row->warehouse->name }}
                @endscope

                @scope('cell_quantity_available', $row)
                    <x-mary-badge value="{{ number_format($row->quantity_available) }}" class="badge-error" />
                @endscope

                @scope('cell_min_stock_level', $row)
                    {{ number_format($row->min_stock_level) }}
                @endscope

                @scope('cell_reorder_quantity', $row)
                    <span class="font-medium text-primary">{{ number_format($row->reorder_quantity) }}</span>
                @endscope

                @scope('cell_cost_estimate', $row)
                    <span class="font-medium">₱{{ number_format($row->reorder_quantity * $row->cost_price, 2) }}</span>
                @endscope
            </x-mary-table>
        @elseif($reportType === 'turnover')
            {{-- Turnover Report Table --}}
            <x-mary-table :headers="[
                ['key' => 'product_name', 'label' => 'Product'],
                ['key' => 'sku', 'label' => 'SKU'],
                ['key' => 'category_name', 'label' => 'Category'],
                ['key' => 'total_sold', 'label' => 'Units Sold'],
                ['key' => 'avg_inventory', 'label' => 'Avg Inventory'],
                ['key' => 'turnover_ratio', 'label' => 'Turnover Ratio'],
                ['key' => 'days_to_sell', 'label' => 'Days to Sell'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_total_sold', $row)
                    {{ number_format($row->total_sold) }}
                @endscope

                @scope('cell_avg_inventory', $row)
                    {{ number_format($row->avg_inventory) }}
                @endscope

                @scope('cell_turnover_ratio', $row)
                    @php
                        $ratio = $row->turnover_ratio;
                        $class = $ratio > 4 ? 'badge-success' : ($ratio > 2 ? 'badge-warning' : 'badge-error');
                    @endphp
                    <x-mary-badge value="{{ number_format($ratio, 2) }}" class="{{ $class }}" />
                @endscope

                @scope('cell_days_to_sell', $row)
                    {{ number_format($row->days_to_sell) }} days
                @endscope
            </x-mary-table>
        @endif
    </x-mary-card>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</div>

@script
    <script>
        let inventoryChart = null;

        function initializeCharts(chartDataInput = null, reportTypeInput = null) {
            const ctx = document.getElementById('inventoryChart');
            if (!ctx) return;

            const chartData = chartDataInput || @json($chartData ?? []);
            const reportType = reportTypeInput || @json($reportType ?? 'stock_levels');

            // Destroy existing chart
            if (inventoryChart) {
                inventoryChart.destroy();
                inventoryChart = null;
            }

            const chartConfig = {
                type: reportType === 'valuation' ? 'bar' : 'doughnut',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    if (reportType === 'valuation') {
                                        return context.dataset.label + ': ₱' + context.parsed.y.toLocaleString();
                                    } else {
                                        return context.label + ': ' + context.parsed + ' items';
                                    }
                                }
                            }
                        }
                    }
                }
            };

            // Add scale config for valuation
            if (reportType === 'valuation') {
                chartConfig.options.scales = {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Value (₱)'
                        }
                    }
                };
            }

            inventoryChart = new Chart(ctx, chartConfig);
        }

        // Initialize on page load
        initializeCharts();

        // Update on Livewire event
        Livewire.on('chartUpdated', (data = {}) => {
            const chartData = data.chartData || data[0]?.chartData;
            const reportType = data.reportType || data[0]?.reportType;

            setTimeout(() => {
                initializeCharts(chartData, reportType);
            }, 100);
        });
    </script>
@endscript
