<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold ">Customer Reports</h1>
            <p>Analyze customer behavior, loyalty, and lifetime value</p>
        </div>
        <div class="flex gap-2">
            <x-mary-button label="Export Excel" icon="o-document-arrow-down" wire:click="exportToExcel"
                class="btn-outline" />
            <x-mary-button label="Refresh Data" icon="o-arrow-path" wire:click="refreshData" class="btn-ghost" />
            <x-mary-button label="Clear Filters" icon="o-x-mark" wire:click="clearFilters" class="btn-ghost" />
        </div>
    </div>

    {{-- Filters Section --}}
    <x-mary-card title="Report Filters" class="mb-6">
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

            {{-- Customer Group Filter --}}
            <x-mary-select label="Customer Group" wire:model.live="customerGroup" :options="$filterOptions['customerGroups']" />

            {{-- Customer Status Filter --}}
            <x-mary-select label="Customer Status" wire:model.live="customerStatus" :options="$filterOptions['customerStatuses']" />
        </div>
    </x-mary-card>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
        <x-mary-stat title="Total Customers" value="{{ number_format($summaryData['total_customers']) }}" icon="o-users"
            color="text-primary" />

        <x-mary-stat title="Total Revenue" value="₱{{ number_format($summaryData['total_revenue'], 2) }}"
            icon="o-banknotes" color="text-success" />

        <x-mary-stat title="Avg Order Value" value="₱{{ number_format($summaryData['avg_order_value'], 2) }}"
            icon="o-calculator" color="text-info" />

        <x-mary-stat title="Retention Rate" value="{{ number_format($summaryData['retention_rate'], 1) }}%"
            icon="o-arrow-path" color="text-warning" />
    </div>

    {{-- Additional Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <x-mary-stat title="New Customers" value="{{ number_format($summaryData['new_customers']) }}" icon="o-user-plus"
            color="text-success" />

        <x-mary-stat title="Returning Customers" value="{{ number_format($summaryData['returning_customers']) }}"
            icon="o-arrow-uturn-left" color="text-primary" />

        <x-mary-stat title="Top Customer Spent" value="₱{{ number_format($summaryData['top_customer_spent'], 2) }}"
            icon="o-star" color="text-warning" />
    </div>

    {{-- Chart Section --}}
    @if ($showChart && in_array($reportType, ['customer_analysis', 'segmentation', 'lifetime_value']))
        <x-mary-card title="Customer Analytics Visualization" class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium">
                    @if ($reportType === 'customer_analysis')
                        New vs Returning Customers Trend
                    @elseif($reportType === 'segmentation')
                        Customer Segmentation Distribution
                    @elseif($reportType === 'lifetime_value')
                        Customer Lifetime Value Distribution
                    @endif
                </h3>
                <x-mary-button icon="{{ $showChart ? 'o-eye-slash' : 'o-eye' }}" wire:click="$toggle('showChart')"
                    class="btn-ghost btn-sm" />
            </div>

            <div class="h-96">
                <canvas id="customerChart"></canvas>
            </div>
        </x-mary-card>
    @endif

    {{-- Report Data Section --}}
    <x-mary-card>
        <x-slot:title>
            <div class="flex items-center justify-between space-x-2">
                <span>{{ collect($filterOptions['reportTypes'])->firstWhere('value', $reportType)['label'] ?? 'Customer Data' }}</span>
                <div class="flex gap-2">
                    @if ($reportType === 'customer_analysis')
                        <x-mary-button label="Sort by {{ $sortBy === 'total_spent' ? 'Orders' : 'Spent' }}"
                            wire:click="sortBy('{{ $sortBy === 'total_spent' ? 'total_orders' : 'total_spent' }}')"
                            wire:key='sortBy{{ $sortBy }}' class="btn-outline btn-sm" />
                    @endif
                </div>
            </div>
        </x-slot:title>

        @if ($reportType === 'customer_analysis')
            {{-- Customer Analysis Table --}}
            <x-mary-table :headers="[
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'total_orders', 'label' => 'Total Orders'],
                ['key' => 'total_spent', 'label' => 'Total Spent'],
                ['key' => 'avg_order_value', 'label' => 'Avg Order Value'],
                ['key' => 'total_profit_generated', 'label' => 'Profit Generated'],
                ['key' => 'last_purchase_date', 'label' => 'Last Purchase'],
                ['key' => 'customer_since', 'label' => 'Customer Since'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_customer', $row)
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="w-8 rounded-full bg-neutral text-neutral-content">
                                <span class="text-xs">{{ substr($row->customer?->name ?? 'W', 0, 1) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="font-medium">{{ $row->customer?->name ?? 'Walk-in Customer' }}</div>
                            @if ($row->customer?->email)
                                <div class="text-sm text-gray-500">{{ $row->customer->email }}</div>
                            @endif
                            @if ($row->customer?->type)
                                <x-mary-badge value="{{ ucfirst($row->customer->type) }}" class="badge-outline badge-xs" />
                            @endif
                        </div>
                    </div>
                @endscope

                @scope('cell_total_orders', $row)
                    <x-mary-badge value="{{ number_format($row->total_orders) }}"
                        class="{{ $row->total_orders >= 10 ? 'badge-success' : ($row->total_orders >= 5 ? 'badge-warning' : 'badge-info') }}" />
                @endscope

                @scope('cell_total_spent', $row)
                    <div class="text-right">
                        <div class="font-medium text-primary">₱{{ number_format($row->total_spent, 2) }}</div>
                        @if ($row->total_spent >= 100000)
                            <div class="text-xs text-success">VIP Customer</div>
                        @elseif($row->total_spent >= 50000)
                            <div class="text-xs text-warning">High Value</div>
                        @endif
                    </div>
                @endscope

                @scope('cell_avg_order_value', $row)
                    <div class="text-right">₱{{ number_format($row->avg_order_value, 2) }}</div>
                @endscope

                @scope('cell_total_profit_generated', $row)
                    <div class="text-right">
                        <span
                            class="font-medium text-success">₱{{ number_format($row->total_profit_generated ?? 0, 2) }}</span>
                    </div>
                @endscope

                @scope('cell_last_purchase_date', $row)
                    <div>
                        <div>
                            {{ $row->last_purchase_date ? Carbon\Carbon::parse($row->last_purchase_date)->format('M j, Y') : 'Never' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $row->last_purchase_date ? Carbon\Carbon::parse($row->last_purchase_date)->diffForHumans() : '' }}
                        </div>
                    </div>
                @endscope

                @scope('cell_customer_since', $row)
                    <div>
                        <div>
                            {{ $row->first_purchase_date ? Carbon\Carbon::parse($row->first_purchase_date)->format('M j, Y') : 'Never' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $row->first_purchase_date ? Carbon\Carbon::parse($row->first_purchase_date)->diffForHumans() : '' }}
                        </div>
                    </div>
                @endscope
            </x-mary-table>
        @elseif($reportType === 'loyalty_analysis')
            {{-- Loyalty Analysis Table --}}
            <x-mary-table :headers="[
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'customer_group', 'label' => 'Group'],
                ['key' => 'total_orders', 'label' => 'Orders'],
                ['key' => 'total_spent', 'label' => 'Total Spent'],
                ['key' => 'loyalty_score', 'label' => 'Loyalty Score'],
                ['key' => 'loyalty_tier', 'label' => 'Loyalty Tier'],
                ['key' => 'status', 'label' => 'Status'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_customer', $row)
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="w-8 rounded-full bg-neutral text-neutral-content">
                                <span class="text-xs">{{ substr($row->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="font-medium">{{ $row->name }}</div>
                            @if ($row->email)
                                <div class="text-sm text-gray-500">{{ $row->email }}</div>
                            @endif
                        </div>
                    </div>
                @endscope

                @scope('cell_customer_group', $row)
                    <x-mary-badge value="{{ ucfirst($row->type ?? 'Regular') }}" class="badge-outline" />
                @endscope

                @scope('cell_total_orders', $row)
                    <div class="text-center">{{ number_format($row->total_orders) }}</div>
                @endscope

                @scope('cell_total_spent', $row)
                    <div class="text-right">
                        <span class="font-medium">₱{{ number_format($row->total_spent, 2) }}</span>
                    </div>
                @endscope

                @scope('cell_loyalty_score', $row)
                    @php
                        $score = $row->loyalty_score;
                        $class =
                            $score >= 80
                                ? 'badge-success'
                                : ($score >= 60
                                    ? 'badge-warning'
                                    : ($score >= 40
                                        ? 'badge-info'
                                        : 'badge-error'));
                    @endphp
                    <div class="text-center">
                        <x-mary-badge value="{{ $score }}" class="{{ $class }}" />
                        <div class="w-full h-2 mt-1 bg-gray-200 rounded-full">
                            <div class="h-2 bg-current rounded-full" style="width: {{ $score }}%"></div>
                        </div>
                    </div>
                @endscope

                @scope('cell_loyalty_tier', $row)
                    @php
                        $score = $row->loyalty_score;
                        if ($score >= 80) {
                            $tier = 'Champion';
                            $class = 'badge-success';
                        } elseif ($score >= 60) {
                            $tier = 'Loyal';
                            $class = 'badge-primary';
                        } elseif ($score >= 40) {
                            $tier = 'Potential';
                            $class = 'badge-warning';
                        } else {
                            $tier = 'At Risk';
                            $class = 'badge-error';
                        }
                    @endphp
                    <x-mary-badge value="{{ $tier }}" class="{{ $class }}" />
                @endscope

                @scope('cell_status', $row)
                    <x-mary-badge value="{{ $row->is_active ? 'Active' : 'Inactive' }}"
                        class="{{ $row->is_active ? 'badge-success' : 'badge-error' }}" />
                @endscope
            </x-mary-table>
        @elseif($reportType === 'segmentation')
            {{-- Customer Segmentation Table --}}
            <x-mary-table :headers="[
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'monetary_value', 'label' => 'Total Spent'],
                ['key' => 'frequency', 'label' => 'Purchase Frequency'],
                ['key' => 'recency_days', 'label' => 'Days Since Last Purchase'],
                ['key' => 'segment', 'label' => 'Customer Segment'],
                ['key' => 'rfm_scores', 'label' => 'RFM Scores'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_customer', $row)
                    <div>
                        <div class="font-medium">{{ $row->customer?->name ?? 'Walk-in Customer' }}</div>
                        @if ($row->customer?->email)
                            <div class="text-sm text-gray-500">{{ $row->customer->email }}</div>
                        @endif
                    </div>
                @endscope

                @scope('cell_monetary_value', $row)
                    <div class="text-right">
                        <span class="font-medium">₱{{ number_format($row->monetary_value, 2) }}</span>
                    </div>
                @endscope

                @scope('cell_frequency', $row)
                    <div class="text-center">
                        <span class="font-medium">{{ number_format($row->frequency) }}</span>
                        <div class="text-xs text-gray-500">orders</div>
                    </div>
                @endscope

                @scope('cell_recency_days', $row)
                    @php
                        $days = $row->recency_days;
                        $class = $days <= 30 ? 'badge-success' : ($days <= 90 ? 'badge-warning' : 'badge-error');
                        $status = $days <= 30 ? 'Recent' : ($days <= 90 ? 'Moderate' : 'Distant');
                    @endphp
                    <div class="text-center">
                        <x-mary-badge value="{{ $days }} days" class="{{ $class }}" />
                        <div class="text-xs text-gray-500">{{ $status }}</div>
                    </div>
                @endscope

                @scope('cell_segment', $row)
                    @php
                        $segmentColors = [
                            'Champions' => 'badge-success',
                            'Loyal Customers' => 'badge-primary',
                            'Potential Loyalists' => 'badge-info',
                            'New Customers' => 'badge-accent',
                            'Promising' => 'badge-warning',
                            'Need Attention' => 'badge-warning',
                            'About to Sleep' => 'badge-error',
                            'At Risk' => 'badge-error',
                        ];
                        $class = $segmentColors[$row->segment] ?? 'badge-neutral';
                    @endphp
                    <x-mary-badge value="{{ $row->segment }}" class="{{ $class }}" />
                @endscope

                @scope('cell_rfm_scores', $row)
                    <div class="text-center">
                        <div class="grid grid-cols-3 gap-1 text-xs">
                            <div class="px-1 text-red-800 bg-red-100 rounded">R:{{ $row->recency_score }}</div>
                            <div class="px-1 text-blue-800 bg-blue-100 rounded">F:{{ $row->frequency_score }}</div>
                            <div class="px-1 text-green-800 bg-green-100 rounded">M:{{ $row->monetary_score }}</div>
                        </div>
                    </div>
                @endscope
            </x-mary-table>
        @elseif($reportType === 'lifetime_value')
            {{-- Customer Lifetime Value Table --}}
            <x-mary-table :headers="[
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'total_orders', 'label' => 'Total Orders'],
                ['key' => 'lifetime_value', 'label' => 'Actual CLV'],
                ['key' => 'avg_order_value', 'label' => 'Avg Order Value'],
                ['key' => 'customer_lifespan_days', 'label' => 'Customer Lifespan'],
                ['key' => 'predicted_clv', 'label' => 'Predicted CLV'],
                ['key' => 'clv_category', 'label' => 'CLV Category'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_customer', $row)
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="w-8 rounded-full bg-neutral text-neutral-content">
                                <span class="text-xs">{{ substr($row->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="font-medium">{{ $row->name }}</div>
                            @if ($row->email)
                                <div class="text-sm text-gray-500">{{ $row->email }}</div>
                            @endif
                        </div>
                    </div>
                @endscope

                @scope('cell_total_orders', $row)
                    <div class="text-center">{{ number_format($row->total_orders) }}</div>
                @endscope

                @scope('cell_lifetime_value', $row)
                    <div class="text-right">
                        <span class="font-medium text-primary">₱{{ number_format($row->lifetime_value, 2) }}</span>
                    </div>
                @endscope

                @scope('cell_avg_order_value', $row)
                    <div class="text-right">₱{{ number_format($row->avg_order_value, 2) }}</div>
                @endscope

                @scope('cell_customer_lifespan_days', $row)
                    <div class="text-center">
                        <span>{{ number_format($row->customer_lifespan_days) }}</span>
                        <div class="text-xs text-gray-500">days</div>
                    </div>
                @endscope

                @scope('cell_predicted_clv', $row)
                    <div class="text-right">
                        <span class="font-medium text-success">₱{{ number_format($row->predicted_clv, 2) }}</span>
                        @php
                            $growth =
                                $row->lifetime_value > 0
                                    ? (($row->predicted_clv - $row->lifetime_value) / $row->lifetime_value) * 100
                                    : 0;
                        @endphp
                        @if ($growth != 0)
                            <div class="text-xs {{ $growth > 0 ? 'text-success' : 'text-error' }}">
                                {{ $growth > 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                            </div>
                        @endif
                    </div>
                @endscope

                @scope('cell_clv_category', $row)
                    @php
                        $clv = $row->predicted_clv;
                        if ($clv >= 200000) {
                            $category = 'Platinum';
                            $class = 'badge-warning';
                        } elseif ($clv >= 100000) {
                            $category = 'Gold';
                            $class = 'badge-success';
                        } elseif ($clv >= 50000) {
                            $category = 'Silver';
                            $class = 'badge-info';
                        } elseif ($clv >= 20000) {
                            $category = 'Bronze';
                            $class = 'badge-accent';
                        } else {
                            $category = 'Standard';
                            $class = 'badge-neutral';
                        }
                    @endphp
                    <x-mary-badge value="{{ $category }}" class="{{ $class }}" />
                @endscope
            </x-mary-table>
        @elseif($reportType === 'product_preferences')
            {{-- Product Preferences Table --}}
            <x-mary-table :headers="[
                ['key' => 'customer_name', 'label' => 'Customer'],
                ['key' => 'preferred_category', 'label' => 'Preferred Category'],
                ['key' => 'category_purchases', 'label' => 'Purchases in Category'],
                ['key' => 'total_quantity', 'label' => 'Total Quantity'],
                ['key' => 'total_spent_in_category', 'label' => 'Total Spent'],
                ['key' => 'avg_price_point', 'label' => 'Avg Price Point'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_customer_name', $row)
                    <div>
                        <div class="font-medium">{{ $row->customer_name ?? 'Walk-in Customer' }}</div>
                        @if ($row->customer_email)
                            <div class="text-sm text-gray-500">{{ $row->customer_email }}</div>
                        @endif
                    </div>
                @endscope

                @scope('cell_preferred_category', $row)
                    <x-mary-badge value="{{ $row->preferred_category }}" class="badge-primary" />
                @endscope

                @scope('cell_category_purchases', $row)
                    <div class="text-center">{{ number_format($row->category_purchases) }}</div>
                @endscope

                @scope('cell_total_quantity', $row)
                    <div class="text-center">{{ number_format($row->total_quantity) }}</div>
                @endscope

                @scope('cell_total_spent_in_category', $row)
                    <div class="text-right">
                        <span
                            class="font-medium text-primary">₱{{ number_format($row->total_spent_in_category, 2) }}</span>
                    </div>
                @endscope

                @scope('cell_avg_price_point', $row)
                    <div class="text-right">₱{{ number_format($row->avg_price_point, 2) }}</div>
                @endscope
            </x-mary-table>
        @elseif($reportType === 'purchase_behavior')
            {{-- Purchase Behavior Table --}}
            <x-mary-table :headers="[
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'purchase_frequency', 'label' => 'Purchase Frequency'],
                ['key' => 'avg_purchase_amount', 'label' => 'Avg Purchase Amount'],
                ['key' => 'purchase_variance', 'label' => 'Purchase Variance'],
                ['key' => 'preferred_time', 'label' => 'Preferred Shopping Time'],
                ['key' => 'avg_days_between_purchases', 'label' => 'Purchase Cycle'],
            ]" :rows="$reportData" with-pagination>
                @scope('cell_customer', $row)
                    <div>
                        <div class="font-medium">{{ $row->customer?->name ?? 'Walk-in Customer' }}</div>
                        @if ($row->customer?->email)
                            <div class="text-sm text-gray-500">{{ $row->customer->email }}</div>
                        @endif
                    </div>
                @endscope

                @scope('cell_purchase_frequency', $row)
                    @php
                        $freq = $row->purchase_frequency;
                        $class = $freq >= 20 ? 'badge-success' : ($freq >= 10 ? 'badge-warning' : 'badge-info');
                        $label = $freq >= 20 ? 'High' : ($freq >= 10 ? 'Medium' : 'Low');
                    @endphp
                    <div class="text-center">
                        <x-mary-badge value="{{ number_format($freq) }}" class="{{ $class }}" />
                        <div class="text-xs text-gray-500">{{ $label }} Frequency</div>
                    </div>
                @endscope

                @scope('cell_avg_purchase_amount', $row)
                    <div class="text-right">₱{{ number_format($row->avg_purchase_amount, 2) }}</div>
                @endscope

                @scope('cell_purchase_variance', $row)
                    @php
                        $variance = $row->purchase_variance ?? 0;
                        $consistency = $variance < 1000 ? 'Consistent' : ($variance < 5000 ? 'Moderate' : 'Variable');
                        $class = $variance < 1000 ? 'text-success' : ($variance < 5000 ? 'text-warning' : 'text-error');
                    @endphp
                    <div class="text-right">
                        <span>₱{{ number_format($variance, 2) }}</span>
                        <div class="text-xs {{ $class }}">{{ $consistency }}</div>
                    </div>
                @endscope

                @scope('cell_preferred_time', $row)
                    @php
                        $hour = $row->preferred_hour ?? 12;
                        $timeLabel = $hour < 12 ? 'Morning' : ($hour < 17 ? 'Afternoon' : 'Evening');
                    @endphp
                    <div class="text-center">
                        <span>{{ number_format($hour) }}:00</span>
                        <div class="text-xs text-gray-500">{{ $timeLabel }}</div>
                    </div>
                @endscope

                @scope('cell_avg_days_between_purchases', $row)
                    @php
                        $days = $row->avg_days_between_purchases ?? 0;
                        $cycle = $days <= 30 ? 'Frequent' : ($days <= 90 ? 'Regular' : 'Occasional');
                        $class = $days <= 30 ? 'text-success' : ($days <= 90 ? 'text-warning' : 'text-info');
                    @endphp
                    <div class="text-center">
                        <span>{{ number_format($days, 1) }}</span>
                        <div class="text-xs {{ $class }}">{{ $cycle }}</div>
                    </div>
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
        let customerChart = null;

        function initializeCharts(chartDataInput = null, reportTypeInput = null) {
            const ctx = document.getElementById('customerChart');
            if (!ctx) return;

            const chartData = chartDataInput || @json($chartData ?? []);
            const reportType = reportTypeInput || @json($reportType ?? 'distribution');

            // Destroy existing chart
            if (customerChart) {
                customerChart.destroy();
                customerChart = null;
            }

            let chartConfig = {
                type: reportType === 'customer_analysis' ? 'bar' : 'doughnut',
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
                                    if (reportType === 'customer_analysis') {
                                        return context.dataset.label + ': ' + context.parsed.y + ' customers';
                                    } else {
                                        return context.label + ': ' + context.parsed + ' customers';
                                    }
                                }
                            }
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            };

            // Add axis config for bar-type chart
            if (reportType === 'customer_analysis') {
                chartConfig.options.scales = {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Customers'
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Time Period'
                        }
                    }
                };
            }

            customerChart = new Chart(ctx, chartConfig);
        }

        // Initialize after Livewire navigation (SPA behavior)
        initializeCharts();

        // Livewire-driven dynamic update
        Livewire.on('chartUpdated', (data = {}) => {
            const chartData = data.chartData || data[0]?.chartData;
            const reportType = data.reportType || data[0]?.reportType;

            setTimeout(() => {
                initializeCharts(chartData, reportType);
            }, 100);
        });
    </script>
@endscript
