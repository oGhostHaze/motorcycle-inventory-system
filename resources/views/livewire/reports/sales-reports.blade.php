<div>
    {{-- Header Section --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sales Reports</h1>
            <p class="text-gray-600">Comprehensive sales analytics and performance insights</p>
        </div>
        <div class="flex gap-2">
            <x-mary-button icon="o-arrow-path" wire:click="loadReportData" spinner="loadReportData" class="btn-outline">
                Refresh
            </x-mary-button>
            <x-mary-button icon="o-document-arrow-down" @click="$wire.showExportModal = true" class="btn-primary">
                Export Report
            </x-mary-button>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="p-4 mb-6 rounded-lg bg-base-200">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
            {{-- Date Range --}}
            <x-mary-select label="Date Range" wire:model="dateRange" :options="$dateRangeOptions" placeholder="Select period" />

            {{-- Custom Date Range --}}
            @if ($dateRange === 'custom')
                <x-mary-datetime label="Start Date" wire:model="startDate" type="date" />
                <x-mary-datetime label="End Date" wire:model="endDate" type="date" />
            @endif

            {{-- Warehouse Filter --}}
            <x-mary-select label="Warehouse" wire:model="warehouseFilter" :options="$warehouses" option-value="id"
                option-label="name" placeholder="All Warehouses" />

            {{-- Status Filter --}}
            <x-mary-select label="Status" wire:model="statusFilter" :options="$statusOptions" placeholder="Select status" />

            {{-- Payment Method --}}
            <x-mary-select label="Payment Method" wire:model="paymentMethodFilter" :options="$paymentMethodOptions"
                placeholder="All Methods" />
        </div>

        <div class="flex gap-2 mt-4">
            <x-mary-button wire:click="applyFilters" icon="o-funnel" class="btn-primary btn-sm">
                Apply Filters
            </x-mary-button>
            <x-mary-button wire:click="resetFilters" icon="o-x-mark" class="btn-outline btn-sm">
                Reset
            </x-mary-button>
        </div>
    </div>

    {{-- Report Type Tabs --}}
    <x-mary-tabs wire:model="reportType" class="mb-6">
        <x-mary-tab name="overview" label="Overview" icon="o-chart-pie">
            {{-- Sales Summary Cards --}}
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
                <x-mary-stat title="Total Sales" value="{{ number_format($salesSummary['total_sales'] ?? 0) }}"
                    icon="o-shopping-cart" color="text-primary" />

                <x-mary-stat title="Total Revenue" value="₱{{ number_format($salesSummary['total_revenue'] ?? 0, 2) }}"
                    icon="o-banknotes" color="text-success" />

                <x-mary-stat title="Average Sale" value="₱{{ number_format($salesSummary['average_sale'] ?? 0, 2) }}"
                    icon="o-calculator" color="text-info" />

                <x-mary-stat title="Items Sold" value="{{ number_format($salesSummary['total_items_sold'] ?? 0) }}"
                    icon="o-cube" color="text-warning" />
            </div>

            {{-- Profit Summary Cards --}}
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
                <x-mary-stat title="Total Profit" value="₱{{ number_format($profitSummary['total_profit'] ?? 0, 2) }}"
                    icon="o-arrow-trending-up" color="text-success" />

                <x-mary-stat title="Profit Margin" value="{{ number_format($profitSummary['profit_margin'] ?? 0, 2) }}%"
                    icon="o-chart-bar" color="text-primary" />

                <x-mary-stat title="Cost of Goods" value="₱{{ number_format($profitSummary['total_cost'] ?? 0, 2) }}"
                    icon="o-minus-circle" color="text-error" />

                <x-mary-stat title="Avg Profit/Sale"
                    value="₱{{ number_format($profitSummary['average_profit_per_sale'] ?? 0, 2) }}"
                    icon="o-currency-dollar" color="text-success" />
            </div>

            {{-- Period Comparison --}}
            @if (!empty($dailyComparison))
                <div class="p-4 mb-6 rounded-lg bg-base-200">
                    <h3 class="mb-4 text-lg font-semibold">Period Comparison</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="p-4 rounded-lg bg-base-100">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Revenue Change</span>
                                <span
                                    class="text-lg font-bold {{ $dailyComparison['revenue_change'] >= 0 ? 'text-success' : 'text-error' }}">
                                    {{ $dailyComparison['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($dailyComparison['revenue_change'], 1) }}%
                                </span>
                            </div>
                            <div class="text-xs text-gray-500">
                                Previous: ₱{{ number_format($dailyComparison['previous_revenue'], 2) }}
                            </div>
                        </div>
                        <div class="p-4 rounded-lg bg-base-100">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Sales Count Change</span>
                                <span
                                    class="text-lg font-bold {{ $dailyComparison['count_change'] >= 0 ? 'text-success' : 'text-error' }}">
                                    {{ $dailyComparison['count_change'] >= 0 ? '+' : '' }}{{ number_format($dailyComparison['count_change'], 1) }}%
                                </span>
                            </div>
                            <div class="text-xs text-gray-500">
                                Previous: {{ number_format($dailyComparison['previous_count']) }} sales
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Sales Trends Chart --}}
            <div class="p-4 mb-6 rounded-lg bg-base-100">
                <h3 class="mb-4 text-lg font-semibold">Sales Trends</h3>
                <div class="h-64">
                    <canvas id="salesTrendsChart"></canvas>
                </div>
            </div>

            {{-- Payment Methods Breakdown --}}
            @if (!empty($paymentMethods))
                <div class="p-4 mb-6 rounded-lg bg-base-100">
                    <h3 class="mb-4 text-lg font-semibold">Payment Methods</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        @php $totalPaymentAmount = collect($paymentMethods)->sum('total_amount'); @endphp
                        @foreach ($paymentMethods as $method)
                            @php $percentage = $totalPaymentAmount > 0 ? ($method['total_amount'] / $totalPaymentAmount) * 100 : 0; @endphp
                            <div class="p-3 rounded-lg bg-base-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium">{{ $method['method'] }}</span>
                                    <span class="text-sm text-gray-600">{{ number_format($percentage, 1) }}%</span>
                                </div>
                                <div class="text-lg font-bold text-primary">
                                    ₱{{ number_format($method['total_amount'], 2) }}</div>
                                <div class="text-sm text-gray-600">{{ $method['count'] }} transactions</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-mary-tab>

        <x-mary-tab name="products" label="Products" icon="o-cube">
            {{-- Top Products Table --}}
            <div class="overflow-x-auto">
                <x-mary-table :headers="[
                    ['key' => 'name', 'label' => 'Product'],
                    ['key' => 'sku', 'label' => 'SKU'],
                    ['key' => 'quantity', 'label' => 'Qty Sold'],
                    ['key' => 'revenue', 'label' => 'Revenue'],
                    ['key' => 'profit', 'label' => 'Profit'],
                    ['key' => 'profit_margin', 'label' => 'Margin %'],
                    ['key' => 'total_sales', 'label' => 'Sales Count'],
                ]" :rows="$topProducts" class="table-zebra">
                    @scope('cell_name', $product)
                        <div>
                            <div class="font-medium">{{ $product['name'] }}</div>
                        </div>
                    @endscope

                    @scope('cell_quantity', $product)
                        <x-mary-badge value="{{ number_format($product['quantity']) }}" class="badge-info" />
                    @endscope

                    @scope('cell_revenue', $product)
                        <span class="font-medium text-success">₱{{ number_format($product['revenue'], 2) }}</span>
                    @endscope

                    @scope('cell_profit', $product)
                        <span class="font-medium {{ $product['profit'] >= 0 ? 'text-success' : 'text-error' }}">
                            ₱{{ number_format($product['profit'], 2) }}
                        </span>
                    @endscope

                    @scope('cell_profit_margin', $product)
                        <span class="font-medium {{ $product['profit_margin'] >= 0 ? 'text-success' : 'text-error' }}">
                            {{ number_format($product['profit_margin'], 1) }}%
                        </span>
                    @endscope

                    @scope('cell_total_sales', $product)
                        <x-mary-badge value="{{ $product['total_sales'] }}" class="badge-ghost" />
                    @endscope
                </x-mary-table>
            </div>

            {{-- Category Performance --}}
            @if (!empty($categoryPerformance))
                <div class="mt-6">
                    <h3 class="mb-4 text-lg font-semibold">Category Performance</h3>
                    <div class="overflow-x-auto">
                        <x-mary-table :headers="[
                            ['key' => 'name', 'label' => 'Category'],
                            ['key' => 'total_quantity', 'label' => 'Items Sold'],
                            ['key' => 'total_revenue', 'label' => 'Revenue'],
                            ['key' => 'total_sales', 'label' => 'Transactions'],
                        ]" :rows="$categoryPerformance" class="table-zebra">
                            @scope('cell_total_quantity', $category)
                                <x-mary-badge value="{{ number_format($category['total_quantity']) }}"
                                    class="badge-info" />
                            @endscope

                            @scope('cell_total_revenue', $category)
                                <span
                                    class="font-medium text-success">₱{{ number_format($category['total_revenue'], 2) }}</span>
                            @endscope

                            @scope('cell_total_sales', $category)
                                <x-mary-badge value="{{ $category['total_sales'] }}" class="badge-ghost" />
                            @endscope
                        </x-mary-table>
                    </div>
                </div>
            @endif
        </x-mary-tab>

        <x-mary-tab name="customers" label="Customers" icon="o-users">
            {{-- Top Customers Table --}}
            <div class="overflow-x-auto">
                <x-mary-table :headers="[
                    ['key' => 'name', 'label' => 'Customer'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'total_orders', 'label' => 'Orders'],
                    ['key' => 'total_spent', 'label' => 'Total Spent'],
                    ['key' => 'avg_order_value', 'label' => 'Avg Order'],
                    ['key' => 'last_order_date', 'label' => 'Last Order'],
                ]" :rows="$topCustomers" class="table-zebra">
                    @scope('cell_name', $customer)
                        <div>
                            <div class="font-medium">{{ $customer['name'] }}</div>
                            @if ($customer['phone'])
                                <div class="text-sm text-gray-600">{{ $customer['phone'] }}</div>
                            @endif
                        </div>
                    @endscope

                    @scope('cell_total_orders', $customer)
                        <x-mary-badge value="{{ $customer['total_orders'] }}" class="badge-info" />
                    @endscope

                    @scope('cell_total_spent', $customer)
                        <span class="font-medium text-success">₱{{ number_format($customer['total_spent'], 2) }}</span>
                    @endscope

                    @scope('cell_avg_order_value', $customer)
                        <span class="font-medium">₱{{ number_format($customer['avg_order_value'], 2) }}</span>
                    @endscope

                    @scope('cell_last_order_date', $customer)
                        <span
                            class="text-sm">{{ \Carbon\Carbon::parse($customer['last_order_date'])->format('M j, Y') }}</span>
                    @endscope
                </x-mary-table>
            </div>
        </x-mary-tab>

        <x-mary-tab name="trends" label="Trends" icon="o-presentation-chart-line">
            {{-- Hourly Trends --}}
            @if (!empty($hourlyTrends))
                <div class="p-4 mb-6 rounded-lg bg-base-100">
                    <h3 class="mb-4 text-lg font-semibold">Hourly Sales Pattern</h3>
                    <div class="h-64">
                        <canvas id="hourlyTrendsChart"></canvas>
                    </div>
                </div>
            @endif

            {{-- Daily Trends Table --}}
            @if (!empty($salesTrends))
                <div class="overflow-x-auto">
                    <x-mary-table :headers="[
                        ['key' => 'date', 'label' => 'Date/Period'],
                        ['key' => 'sales_count', 'label' => 'Sales Count'],
                        ['key' => 'total_amount', 'label' => 'Revenue'],
                    ]" :rows="$salesTrends" class="table-zebra">
                        @scope('cell_date', $trend)
                            <span class="font-medium">
                                @if (isset($trend['date']))
                                    {{ \Carbon\Carbon::parse($trend['date'])->format('M j, Y') }}
                                @else
                                    {{ \Carbon\Carbon::parse($trend['week_start'])->format('M j') }} -
                                    {{ \Carbon\Carbon::parse($trend['week_end'])->format('M j') }}
                                @endif
                            </span>
                        @endscope

                        @scope('cell_sales_count', $trend)
                            <x-mary-badge value="{{ $trend['sales_count'] }}" class="badge-info" />
                        @endscope

                        @scope('cell_total_amount', $trend)
                            <span class="font-medium text-success">₱{{ number_format($trend['total_amount'], 2) }}</span>
                        @endscope
                    </x-mary-table>
                </div>
            @endif
        </x-mary-tab>

        <x-mary-tab name="users" label="Staff" icon="o-user-group">
            {{-- Sales by User Table --}}
            <div class="overflow-x-auto">
                <x-mary-table :headers="[
                    ['key' => 'name', 'label' => 'Staff Member'],
                    ['key' => 'role', 'label' => 'Role'],
                    ['key' => 'total_sales', 'label' => 'Sales Count'],
                    ['key' => 'total_amount', 'label' => 'Total Amount'],
                    ['key' => 'avg_sale_amount', 'label' => 'Avg Sale'],
                ]" :rows="$salesByUser" class="table-zebra">
                    @scope('cell_name', $user)
                        <div class="font-medium">{{ $user['name'] }}</div>
                    @endscope

                    @scope('cell_role', $user)
                        <x-mary-badge value="{{ ucfirst(str_replace('_', ' ', $user['role'])) }}"
                            class="badge-outline" />
                    @endscope

                    @scope('cell_total_sales', $user)
                        <x-mary-badge value="{{ $user['total_sales'] }}" class="badge-info" />
                    @endscope

                    @scope('cell_total_amount', $user)
                        <span class="font-medium text-success">₱{{ number_format($user['total_amount'], 2) }}</span>
                    @endscope

                    @scope('cell_avg_sale_amount', $user)
                        <span class="font-medium">₱{{ number_format($user['avg_sale_amount'], 2) }}</span>
                    @endscope
                </x-mary-table>
            </div>
        </x-mary-tab>
    </x-mary-tabs>

    {{-- Export Modal --}}

    <x-mary-modal wire:model="showExportModal" title="Export Sales Report">
        <div class="space-y-4">
            <div class="p-3 rounded-lg bg-base-200">
                <div class="text-sm font-medium">Export Format:</div>
                <div class="text-lg font-semibold text-primary">Excel (.xlsx)</div>
                <div class="text-sm text-gray-600">Comprehensive spreadsheet with multiple sheets</div>
            </div>

            <div class="p-3 rounded-lg bg-base-200">
                <div class="text-sm font-medium">Export Period:</div>
                <div class="text-sm text-gray-600">
                    {{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} -
                    {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}
                </div>
            </div>

            <div class="p-3 border rounded-lg bg-info/10 border-info/20">
                <div class="flex items-start gap-2">
                    <x-mary-icon name="o-information-circle" class="w-5 h-5 text-info mt-0.5" />
                    <div class="text-sm">
                        <div class="font-medium text-info">Excel Export Includes:</div>
                        <ul class="mt-1 text-xs list-disc list-inside text-info/80">
                            <li>Summary metrics & period comparison</li>
                            <li>Top products & customer analysis</li>
                            <li>Sales trends & hourly patterns</li>
                            <li>Staff performance & category breakdown</li>
                            <li>Payment methods & detailed data</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.showExportModal = false" />
            <x-mary-button label="Export to Excel" wire:click="exportReport" class="btn-primary"
                icon="o-document-arrow-down" />
        </x-slot:actions>
    </x-mary-modal>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

</div>
@script
    <script>
        let salesTrendsChart = null;
        let hourlyTrendsChart = null;

        function initializeCharts(salesDataInput = null, hourlyDataInput = null) {
            // Destroy existing charts
            if (salesTrendsChart) {
                salesTrendsChart.destroy();
                salesTrendsChart = null;
            }
            if (hourlyTrendsChart) {
                hourlyTrendsChart.destroy();
                hourlyTrendsChart = null;
            }

            // Sales Trends Chart
            const salesChartData = salesDataInput || @json($salesChartData ?? []);
            if (salesChartData.length > 0) {
                const salesCtx = document.getElementById('salesTrendsChart');
                if (salesCtx) {
                    const labels = salesChartData.map(item => item.date);
                    const data = salesChartData.map(item => item.sales);

                    salesTrendsChart = new Chart(salesCtx, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Revenue',
                                data,
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.1,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: (value) => '₱' + value.toLocaleString()
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: (context) => 'Revenue: ₱' + context.parsed.y.toLocaleString()
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // Hourly Trends Chart
            const trendsChartData = hourlyDataInput || @json($trendsChartData ?? []);
            if (trendsChartData.length > 0) {
                const hourlyCtx = document.getElementById('hourlyTrendsChart');
                if (hourlyCtx) {
                    const labels = trendsChartData.map(item => item.hour);
                    const data = trendsChartData.map(item => item.sales);

                    hourlyTrendsChart = new Chart(hourlyCtx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Sales',
                                data,
                                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                borderColor: 'rgb(34, 197, 94)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: (value) => '₱' + value.toLocaleString()
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: (context) => 'Revenue: ₱' + context.parsed.y.toLocaleString()
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }

        // Initialize charts on page load
        initializeCharts();

        // Re-initialize charts when Livewire triggers event
        Livewire.on('refresh-charts', (data) => {
            const salesData = data.salesChartData || data[0]?.salesChartData;
            const hourlyData = data.trendsChartData || data[0]?.trendsChartData;

            setTimeout(() => {
                initializeCharts(salesData, hourlyData);
            }, 100);
        });

        // Handle export link trigger
        Livewire.on('trigger-download', function(data) {
            const link = document.createElement('a');
            link.href = data[0].url;
            link.download = data[0].filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
@endscript
