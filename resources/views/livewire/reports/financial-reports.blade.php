<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Financial Reports</h1>
            <p>Track profitability, costs, and financial performance</p>
        </div>
        <div class="flex gap-2">
            <x-mary-button label="Export Excel" icon="o-document-arrow-down" wire:click="exportToExcel"
                class="btn-outline" />
            <x-mary-button label="Clear Filters" icon="o-x-mark" wire:click="clearFilters" class="btn-ghost" />
            <x-mary-toggle label="Compare with Previous" wire:model.live="compareWithPrevious" />
        </div>
    </div>

    {{-- Filters Section --}}
    <x-mary-card title="Filters" class="mb-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            {{-- Report Type --}}
            <x-mary-select label="Report Type" wire:model.live="reportType" :options="$filterOptions['reportTypes']" />

            {{-- Date Period --}}
            <x-mary-select label="Period" wire:model.live="period" :options="$filterOptions['periods']" />

            {{-- Custom Date Range --}}
            @if ($period === 'custom')
                <x-mary-datetime label="Date From" wire:model.live="dateFrom" type="date" />
                <x-mary-datetime label="Date To" wire:model.live="dateTo" type="date" />
            @endif

            {{-- Warehouse Filter --}}
            <x-mary-select label="Warehouse" wire:model.live="warehouse" :options="$filterOptions['warehouses']"
                placeholder="All Warehouses" />

            {{-- Category Filter --}}
            <x-mary-select label="Category" wire:model.live="category" :options="$filterOptions['categories']" placeholder="All Categories" />
        </div>
    </x-mary-card>

    {{-- Financial Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-5">
        <x-mary-stat title="Total Revenue" value="₱{{ number_format($summaryData['total_revenue'], 2) }}"
            icon="o-banknotes" color="text-success" />

        <x-mary-stat title="Total Expenses" value="₱{{ number_format($summaryData['total_expenses'], 2) }}"
            icon="o-arrow-trending-down" color="text-error" />

        <x-mary-stat title="Net Income" value="₱{{ number_format($summaryData['net_income'], 2) }}"
            icon="o-chart-bar-square" color="{{ $summaryData['net_income'] >= 0 ? 'text-success' : 'text-error' }}" />

        <x-mary-stat title="Profit Margin" value="{{ number_format($summaryData['profit_margin'], 1) }}%"
            icon="o-chart-pie" color="text-primary" />

        <x-mary-stat title="ROI" value="{{ number_format($summaryData['roi'], 1) }}%" icon="o-arrow-trending-up"
            color="{{ $summaryData['roi'] >= 0 ? 'text-success' : 'text-error' }}" />
    </div>

    {{-- Comparison Cards (if enabled) --}}
    @if ($compareWithPrevious && $comparisonData)
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
            @php
                $revenueGrowth =
                    $comparisonData['previous_revenue'] > 0
                        ? (($summaryData['total_revenue'] - $comparisonData['previous_revenue']) /
                                $comparisonData['previous_revenue']) *
                            100
                        : 0;
                $profitGrowth =
                    $comparisonData['previous_profit'] > 0
                        ? (($summaryData['total_profit'] - $comparisonData['previous_profit']) /
                                $comparisonData['previous_profit']) *
                            100
                        : 0;
            @endphp

            <x-mary-card class="p-4">
                <div class="text-center">
                    <div class="text-sm text-gray-600">Revenue Growth</div>
                    <div class="text-xl font-bold {{ $revenueGrowth >= 0 ? 'text-success' : 'text-error' }}">
                        {{ $revenueGrowth >= 0 ? '+' : '' }}{{ number_format($revenueGrowth, 1) }}%
                    </div>
                </div>
            </x-mary-card>

            <x-mary-card class="p-4">
                <div class="text-center">
                    <div class="text-sm text-gray-600">Profit Growth</div>
                    <div class="text-xl font-bold {{ $profitGrowth >= 0 ? 'text-success' : 'text-error' }}">
                        {{ $profitGrowth >= 0 ? '+' : '' }}{{ number_format($profitGrowth, 1) }}%
                    </div>
                </div>
            </x-mary-card>

            <x-mary-card class="p-4">
                <div class="text-center">
                    <div class="text-sm text-gray-600">Previous Revenue</div>
                    <div class="text-lg font-medium">₱{{ number_format($comparisonData['previous_revenue'], 2) }}</div>
                </div>
            </x-mary-card>

            <x-mary-card class="p-4">
                <div class="text-center">
                    <div class="text-sm text-gray-600">Previous Profit</div>
                    <div class="text-lg font-medium">₱{{ number_format($comparisonData['previous_profit'], 2) }}</div>
                </div>
            </x-mary-card>
        </div>
    @endif

    {{-- Chart Section --}}
    @if ($showChart)
        <x-mary-card title="Financial Trends" class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium">
                    @if ($reportType === 'profit_loss')
                        Profit & Loss Trends
                    @elseif($reportType === 'cost_analysis')
                        Cost Distribution
                    @elseif($reportType === 'margin_analysis')
                        Profit Margins by Category
                    @elseif($reportType === 'cash_flow')
                        Cash Flow Analysis
                    @endif
                </h3>
                <x-mary-button icon="{{ $showChart ? 'o-eye-slash' : 'o-eye' }}" wire:click="$toggle('showChart')"
                    class="btn-ghost btn-sm" />
            </div>

            <div class="h-96">
                <canvas id="financialChart"></canvas>
            </div>
        </x-mary-card>
    @endif

    {{-- Report Data Section --}}
    <x-mary-card>
        <x-slot:title>
            {{ collect($filterOptions['reportTypes'])->firstWhere('value', $reportType)['label'] ?? 'Financial Data' }}
        </x-slot:title>

        @if ($reportType === 'profit_loss')
            {{-- Profit & Loss Table --}}
            <x-mary-table :headers="[
                ['key' => 'date', 'label' => 'Date'],
                ['key' => 'revenue', 'label' => 'Revenue'],
                ['key' => 'cogs', 'label' => 'COGS'],
                ['key' => 'gross_profit', 'label' => 'Gross Profit'],
                ['key' => 'profit_margin', 'label' => 'Margin %'],
                ['key' => 'transactions', 'label' => 'Transactions'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_date', $row)
                    {{ Carbon\Carbon::parse($row->date)->format('M j, Y') }}
                @endscope

                @scope('cell_revenue', $row)
                    <span class="font-medium text-success">₱{{ number_format($row->revenue, 2) }}</span>
                @endscope

                @scope('cell_cogs', $row)
                    <span class="font-medium text-error">₱{{ number_format($row->cogs, 2) }}</span>
                @endscope

                @scope('cell_gross_profit', $row)
                    <span class="font-medium text-primary">₱{{ number_format($row->gross_profit, 2) }}</span>
                @endscope

                @scope('cell_profit_margin', $row)
                    @php $margin = $row->revenue > 0 ? ($row->gross_profit / $row->revenue) * 100 : 0; @endphp
                    <x-mary-badge value="{{ number_format($margin, 1) }}%"
                        class="{{ $margin >= 20 ? 'badge-success' : ($margin >= 10 ? 'badge-warning' : 'badge-error') }}" />
                @endscope

                @scope('cell_transactions', $row)
                    {{ number_format($row->transactions) }}
                @endscope
            </x-mary-table>
        @elseif($reportType === 'cost_analysis')
            {{-- Cost Analysis Table --}}
            <x-mary-table :headers="[
                ['key' => 'category_name', 'label' => 'Category'],
                ['key' => 'total_quantity', 'label' => 'Quantity Sold'],
                ['key' => 'total_cost', 'label' => 'Total Cost'],
                ['key' => 'total_revenue', 'label' => 'Total Revenue'],
                ['key' => 'total_profit', 'label' => 'Total Profit'],
                ['key' => 'avg_unit_cost', 'label' => 'Avg Unit Cost'],
                ['key' => 'avg_selling_price', 'label' => 'Avg Selling Price'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_total_quantity', $row)
                    {{ number_format($row->total_quantity) }}
                @endscope

                @scope('cell_total_cost', $row)
                    <span class="font-medium text-error">₱{{ number_format($row->total_cost, 2) }}</span>
                @endscope

                @scope('cell_total_revenue', $row)
                    <span class="font-medium text-success">₱{{ number_format($row->total_revenue, 2) }}</span>
                @endscope

                @scope('cell_total_profit', $row)
                    <span class="font-medium text-primary">₱{{ number_format($row->total_profit, 2) }}</span>
                @endscope

                @scope('cell_avg_unit_cost', $row)
                    ₱{{ number_format($row->avg_unit_cost, 2) }}
                @endscope

                @scope('cell_avg_selling_price', $row)
                    ₱{{ number_format($row->avg_selling_price, 2) }}
                @endscope
            </x-mary-table>
        @elseif($reportType === 'margin_analysis')
            {{-- Margin Analysis Table --}}
            <x-mary-table :headers="[
                ['key' => 'product_name', 'label' => 'Product'],
                ['key' => 'sku', 'label' => 'SKU'],
                ['key' => 'category_name', 'label' => 'Category'],
                ['key' => 'total_quantity', 'label' => 'Qty Sold'],
                ['key' => 'total_revenue', 'label' => 'Revenue'],
                ['key' => 'total_profit', 'label' => 'Profit'],
                ['key' => 'profit_margin_percentage', 'label' => 'Margin %'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_total_quantity', $row)
                    {{ number_format($row->total_quantity) }}
                @endscope

                @scope('cell_total_revenue', $row)
                    <span class="font-medium text-success">₱{{ number_format($row->total_revenue, 2) }}</span>
                @endscope

                @scope('cell_total_profit', $row)
                    <span class="font-medium text-primary">₱{{ number_format($row->total_profit, 2) }}</span>
                @endscope

                @scope('cell_profit_margin_percentage', $row)
                    <x-mary-badge value="{{ number_format($row->profit_margin_percentage, 1) }}%"
                        class="{{ $row->profit_margin_percentage >= 30 ? 'badge-success' : ($row->profit_margin_percentage >= 15 ? 'badge-warning' : 'badge-error') }}" />
                @endscope
            </x-mary-table>
        @elseif($reportType === 'cash_flow')
            {{-- Cash Flow Table --}}
            <x-mary-table :headers="[
                ['key' => 'week_start', 'label' => 'Week Starting'],
                ['key' => 'cash_in', 'label' => 'Cash In'],
                ['key' => 'cash_out', 'label' => 'Cash Out'],
                ['key' => 'net_flow', 'label' => 'Net Flow'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_week_start', $row)
                    {{ Carbon\Carbon::parse($row->week_start)->format('M j, Y') }}
                @endscope

                @scope('cell_cash_in', $row)
                    <span class="font-medium text-success">₱{{ number_format($row->cash_in, 2) }}</span>
                @endscope

                @scope('cell_cash_out', $row)
                    <span class="font-medium text-error">₱{{ number_format($row->cash_out, 2) }}</span>
                @endscope

                @scope('cell_net_flow', $row)
                    <span class="font-medium {{ $row->net_flow >= 0 ? 'text-success' : 'text-error' }}">
                        ₱{{ number_format($row->net_flow, 2) }}
                    </span>
                @endscope
            </x-mary-table>
        @elseif($reportType === 'roi_analysis')
            {{-- ROI Analysis Table --}}
            <x-mary-table :headers="[
                ['key' => 'category_name', 'label' => 'Category'],
                ['key' => 'total_investment', 'label' => 'Investment'],
                ['key' => 'total_return', 'label' => 'Return'],
                ['key' => 'total_profit', 'label' => 'Profit'],
                ['key' => 'roi_percentage', 'label' => 'ROI %'],
                ['key' => 'transaction_count', 'label' => 'Transactions'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_total_investment', $row)
                    <span class="font-medium text-error">₱{{ number_format($row->total_investment, 2) }}</span>
                @endscope

                @scope('cell_total_return', $row)
                    <span class="font-medium text-success">₱{{ number_format($row->total_return, 2) }}</span>
                @endscope

                @scope('cell_total_profit', $row)
                    <span class="font-medium text-primary">₱{{ number_format($row->total_profit, 2) }}</span>
                @endscope

                @scope('cell_roi_percentage', $row)
                    <x-mary-badge value="{{ number_format($row->roi_percentage, 1) }}%"
                        class="{{ $row->roi_percentage >= 50 ? 'badge-success' : ($row->roi_percentage >= 25 ? 'badge-warning' : 'badge-error') }}" />
                @endscope

                @scope('cell_transaction_count', $row)
                    {{ number_format($row->transaction_count) }}
                @endscope
            </x-mary-table>
        @endif
    </x-mary-card>
</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
@endpush
@script
    <script>
        let financialChart = null;

        function initializeCharts(chartDataInput = null, reportTypeInput = null) {
            const ctx = document.getElementById('financialChart');
            if (!ctx) return;

            const chartData = chartDataInput || @json($chartData ?? []);
            const reportType = reportTypeInput || @json($reportType ?? 'summary');

            // Destroy existing chart
            if (financialChart) {
                financialChart.destroy();
                financialChart = null;
            }

            let chartConfig = {
                type: reportType === 'cost_analysis' ? 'doughnut' : 'bar',
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
                                label: function(context) {
                                    if (reportType === 'cost_analysis') {
                                        return context.label + ': ₱' + context.parsed.toLocaleString();
                                    } else {
                                        return context.dataset.label + ': ₱' + context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                }
            };

            if (reportType !== 'cost_analysis') {
                chartConfig.options.scales = {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (₱)'
                        }
                    }
                };
            }

            financialChart = new Chart(ctx, chartConfig);
        }

        // Initialize on Livewire navigation (SPA behavior)
        initializeCharts();

        // Refresh on Livewire event
        Livewire.on('chartUpdated', (data = {}) => {
            const chartData = data.chartData || data[0]?.chartData;
            const reportType = data.reportType || data[0]?.reportType;

            setTimeout(() => {
                initializeCharts(chartData, reportType);
            }, 100);
        });
    </script>
@endscript
