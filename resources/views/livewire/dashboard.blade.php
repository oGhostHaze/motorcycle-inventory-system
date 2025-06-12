<div x-data="dashboardCharts()" x-init="initCharts()">
    {{-- Page Header --}}
    <x-mary-header title="Dashboard" subtitle="Welcome back, {{ auth()->user()->name }}!" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-path" wire:click="refreshData" class="btn-ghost" tooltip="Refresh Data" />
            <x-mary-dropdown>
                <x-slot:trigger>
                    <x-mary-button icon="o-ellipsis-vertical" class="btn-ghost" />
                </x-slot:trigger>
                <x-mary-menu-item title="Export Report" icon="o-document-arrow-down" />
                <x-mary-menu-item title="Print Dashboard" icon="o-printer" />
                <x-mary-menu-item title="Dashboard Settings" icon="o-cog-6-tooth" />
            </x-mary-dropdown>
        </x-slot:actions>
    </x-mary-header>

    {{-- Key Performance Stats --}}
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
        {{-- Today's Sales --}}
        <x-mary-stat title="Today's Sales" description="Total sales today" value="₱{{ number_format($todaysSales, 2) }}"
            icon="o-currency-dollar" color="text-primary"
            class="shadow-lg bg-gradient-to-r from-primary/10 to-primary/5">
            <x-slot:actions>
                <div class="text-xs {{ $monthlyGrowth >= 0 ? 'text-success' : 'text-error' }}">
                    {{ $monthlyGrowth >= 0 ? '↗' : '↘' }} {{ number_format(abs($monthlyGrowth), 1) }}%
                </div>
            </x-slot:actions>
        </x-mary-stat>

        {{-- Monthly Sales --}}
        <x-mary-stat title="Monthly Sales" description="Sales this month" value="₱{{ number_format($monthSales, 2) }}"
            icon="o-chart-bar" color="text-success" class="shadow-lg bg-gradient-to-r from-success/10 to-success/5" />

        {{-- Total Products --}}
        <x-mary-stat title="Active Products" description="In catalog" value="{{ number_format($totalProducts) }}"
            icon="o-cube" color="text-info" class="shadow-lg bg-gradient-to-r from-info/10 to-info/5" />

        {{-- Inventory Value --}}
        <x-mary-stat title="Inventory Value" description="Total stock worth"
            value="₱{{ number_format($totalInventoryValue, 2) }}" icon="o-banknotes" color="text-warning"
            class="shadow-lg bg-gradient-to-r from-warning/10 to-warning/5" />
    </div>

    {{-- Secondary Stats --}}
    <div class="grid grid-cols-2 gap-4 mb-8 md:grid-cols-4">
        <x-mary-stat title="Customers" value="{{ number_format($totalCustomers) }}" icon="o-user-group"
            class="shadow bg-base-100" />
        <x-mary-stat title="Suppliers" value="{{ number_format($totalSuppliers) }}" icon="o-building-office"
            class="shadow bg-base-100" />
        <x-mary-stat title="Categories" value="{{ number_format($totalCategories) }}" icon="o-tag"
            class="shadow bg-base-100" />
        <x-mary-stat title="Year Sales" value="₱{{ number_format($yearSales, 2) }}" icon="o-calendar-days"
            class="shadow bg-base-100" />
    </div>

    {{-- Alert Cards --}}
    @if ($lowStockItems > 0 || $pendingOrders > 0)
        <div class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-2">
            @if ($lowStockItems > 0)
                <x-mary-alert title="Low Stock Alert"
                    description="{{ $lowStockItems }} products are running low on stock" icon="o-exclamation-triangle"
                    class="shadow-lg alert-warning">
                    <x-slot:actions>
                        <x-mary-button label="View Items" link="{{ route('inventory.low-stock-alerts') }}"
                            size="sm" class="btn-warning" />
                    </x-slot:actions>
                </x-mary-alert>
            @endif

            @if ($pendingOrders > 0)
                <x-mary-alert title="Pending Orders" description="{{ $pendingOrders }} purchase orders need attention"
                    icon="o-document-text" class="shadow-lg alert-info">
                    <x-slot:actions>
                        <x-mary-button label="View Orders" link="{{ route('purchasing.purchase-orders') }}"
                            size="sm" class="btn-info" />
                    </x-slot:actions>
                </x-mary-alert>
            @endif
        </div>
    @endif

    {{-- Main Charts Section --}}
    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-12">
        {{-- Sales Trend Chart --}}
        <div class="lg:col-span-6">
            <x-mary-card title="Sales Trend (Last 30 Days)" subtitle="Daily sales performance" class="h-96">
                <div class="p-4 h-80">
                    <canvas id="salesChart" class="w-full h-full"></canvas>
                </div>
                <x-slot:actions>
                    <x-mary-button icon="o-arrow-top-right-on-square" class="btn-ghost btn-sm" />
                </x-slot:actions>
            </x-mary-card>
        </div>

        {{-- Monthly Revenue Trend --}}
        <div class="lg:col-span-6">
            <x-mary-card title="Monthly Revenue Trend" subtitle="Last 12 months performance" class="h-80">
                <div class="h-64 p-4">
                    <canvas id="monthlyChart" class="w-full h-full"></canvas>
                </div>
            </x-mary-card>
        </div>

        {{-- Category Distribution --}}
        <div class="lg:col-span-6">
            <x-mary-card title="Category Distribution" subtitle="Products by category" class="h-80">
                <div class="h-64 p-4">
                    <canvas id="categoryChart" class="w-full h-full"></canvas>
                </div>
            </x-mary-card>
        </div>

        {{-- Stock Status Chart --}}
        <div class="lg:col-span-6">
            <x-mary-card title="Stock Status" subtitle="Inventory overview" class="h-96">
                <div class="p-4 h-80">
                    <canvas id="stockStatusChart" class="w-full h-full"></canvas>
                </div>
            </x-mary-card>
        </div>
    </div>

    {{-- Data Tables Section --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Recent Sales --}}
        <div class="lg:col-span-12">
            <x-mary-card title="Recent Sales" subtitle="Latest transactions" class="min-h-96">
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                                <tr class="hover">
                                    <td>
                                        <span class="font-mono text-xs">#{{ $sale->sale_number }}</span>
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $sale->customer->name ?? 'Walk-in Customer' }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $sale->customer->phone ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span
                                            class="font-bold text-success">₱{{ number_format($sale->total_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <x-mary-badge
                                            value="{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}"
                                            class="badge-ghost" />
                                    </td>
                                    <td>
                                        <div>{{ $sale->created_at->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $sale->created_at->format('h:i A') }}
                                        </div>
                                    </td>
                                    <td>
                                        <x-mary-badge value="{{ ucfirst($sale->status) }}"
                                            class="{{ $sale->status === 'completed' ? 'badge-success' : 'badge-warning' }}" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-500">
                                        No recent sales
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-slot:actions>
                    <x-mary-button label="View All Sales" link="{{ route('sales.history') }}"
                        class="btn-primary btn-sm" />
                </x-slot:actions>
            </x-mary-card>
        </div>

        {{-- Top Products & Stock Alerts --}}
        <div class="grid grid-cols-1 gap-6 mb-8 lg:col-span-12 lg:grid-cols-2">
            {{-- Top Products --}}
            <x-mary-card title="Top Selling Products" subtitle="This month's best performers" class="mb-6">
                <div class="space-y-3">
                    @forelse($topProducts as $product)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-base-200">
                            <div class="flex-1">
                                <div class="font-medium truncate">{{ $product->name }}</div>
                                <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-primary">{{ $product->total_sold }} sold</div>
                                <div class="text-sm text-gray-500">₱{{ number_format($product->revenue, 2) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <x-heroicon-o-cube class="w-8 h-8 mx-auto text-gray-400" />
                            <p class="mt-2 text-gray-500">No sales data</p>
                        </div>
                    @endforelse
                </div>
            </x-mary-card>

            {{-- Low Stock Products --}}
            <x-mary-card title="Low Stock Alert" subtitle="Products need attention">
                <div class="space-y-3">
                    @forelse($lowStockProducts as $product)
                        <div
                            class="flex items-center justify-between p-3 border rounded-lg bg-warning/10 border-warning/20">
                            <div>
                                <div class="font-medium">{{ $product->name }}</div>
                                <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-warning whitespace-nowrap">
                                    {{ $product->total_stock ?? 0 }} left </div>
                                <div class="text-sm text-gray-500">Min: {{ $product->min_stock_level ?? 0 }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <x-heroicon-o-check-circle class="w-8 h-8 mx-auto text-success" />
                            <p class="mt-2 text-success">All stock levels good</p>
                        </div>
                    @endforelse
                </div>
            </x-mary-card>
        </div>
    </div>

    {{-- Recent Stock Movements & Top Customers --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Recent Stock Movements --}}
        <div class="lg:col-span-6">
            <x-mary-card title="Recent Stock Movements" subtitle="Latest inventory changes">
                <div class="space-y-3">
                    @forelse($recentStockMovements as $movement)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-base-200">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="p-2 rounded-full {{ $movement->type === 'sale' ? 'bg-error/20 text-error' : 'bg-success/20 text-success' }}">
                                    @if ($movement->type === 'sale')
                                        <x-heroicon-o-arrow-down class="w-4 h-4" />
                                    @else
                                        <x-heroicon-o-arrow-up class="w-4 h-4" />
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium">{{ $movement->product->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ ucfirst($movement->type) }} • {{ $movement->warehouse->name }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div
                                    class="font-bold {{ $movement->quantity_changed > 0 ? 'text-success' : 'text-error' }}">
                                    {{ $movement->quantity_changed > 0 ? '+' : '' }}{{ $movement->quantity_changed }}
                                </div>
                                <div class="text-sm text-gray-500">{{ $movement->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <x-heroicon-o-arrow-path class="w-8 h-8 mx-auto text-gray-400" />
                            <p class="mt-2 text-gray-500">No recent movements</p>
                        </div>
                    @endforelse
                </div>
            </x-mary-card>
        </div>

        {{-- Top Customers --}}
        <div class="lg:col-span-6">
            <x-mary-card title="Top Customers" subtitle="This month's best customers">
                <div class="space-y-3">
                    @forelse($topCustomers as $customer)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-base-200">
                            <div class="flex items-center space-x-3">
                                <div class="avatar placeholder">
                                    <div class="w-10 h-10 pt-2 rounded-full bg-primary text-primary-content">
                                        <span class="text-sm font-bold">{{ substr($customer->name, 0, 2) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $customer->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $customer->total_orders }} orders</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-success">₱{{ number_format($customer->total_spent, 2) }}
                                </div>
                                <div class="text-sm text-gray-500">Total spent</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <x-heroicon-o-user-group class="w-8 h-8 mx-auto text-gray-400" />
                            <p class="mt-2 text-gray-500">No customer data</p>
                        </div>
                    @endforelse
                </div>
            </x-mary-card>
        </div>
    </div>

    {{-- Chart.js Integration --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function dashboardCharts() {
            return {
                salesChart: null,
                stockChart: null,
                monthlyChart: null,
                categoryChart: null,

                initCharts() {
                    this.initSalesChart();
                    this.initStockStatusChart();
                    this.initMonthlyChart();
                    this.initCategoryChart();

                    // Listen for refresh events
                    document.addEventListener('refreshCharts', () => {
                        this.refreshAllCharts();
                    });
                },

                initSalesChart() {
                    const ctx = document.getElementById('salesChart').getContext('2d');
                    const salesData = @js($salesChartData);

                    this.salesChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: salesData.map(item => item.date),
                            datasets: [{
                                label: 'Daily Sales',
                                data: salesData.map(item => item.sales),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: 'rgb(59, 130, 246)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: 'white',
                                    bodyColor: 'white',
                                    borderColor: 'rgb(59, 130, 246)',
                                    borderWidth: 1,
                                    callbacks: {
                                        label: function(context) {
                                            return 'Sales: ₱' + context.parsed.y.toLocaleString();
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '₱' + value.toLocaleString();
                                        }
                                    }
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            }
                        }
                    });
                },

                initStockStatusChart() {
                    const ctx = document.getElementById('stockStatusChart').getContext('2d');
                    const stockData = @js($stockStatusData);

                    this.stockChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: stockData.map(item => item.status),
                            datasets: [{
                                data: stockData.map(item => item.count),
                                backgroundColor: stockData.map(item => item.color),
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        fontColor: 'white',
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: 'white',
                                    bodyColor: 'white',
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                                            return context.label + ': ' + context.parsed + ' (' + percentage +
                                                '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                initMonthlyChart() {
                    const ctx = document.getElementById('monthlyChart').getContext('2d');
                    const monthlyData = @js($monthlyChartData);

                    this.monthlyChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: monthlyData.map(item => item.short_month),
                            datasets: [{
                                label: 'Monthly Revenue',
                                data: monthlyData.map(item => item.sales),
                                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                                borderColor: 'rgb(16, 185, 129)',
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: 'white',
                                    bodyColor: 'white',
                                    callbacks: {
                                        label: function(context) {
                                            return 'Revenue: ₱' + context.parsed.y.toLocaleString();
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '₱' + (value / 1000).toFixed(0) + 'K';
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                initCategoryChart() {
                    const ctx = document.getElementById('categoryChart').getContext('2d');
                    const categoryData = @js($categoryDistribution);

                    this.categoryChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: categoryData.map(item => item.name),
                            datasets: [{
                                data: categoryData.map(item => item.products),
                                backgroundColor: categoryData.map(item => item.color),
                                borderWidth: 2,
                                borderColor: '#fff',
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        generateLabels: function(chart) {
                                            const data = chart.data;
                                            if (data.labels.length && data.datasets.length) {
                                                return data.labels.map((label, i) => {
                                                    const value = data.datasets[0].data[i];
                                                    return {
                                                        text: label + ' (' + value + ')',
                                                        fillStyle: data.datasets[0].backgroundColor[i],
                                                        fontColor: data.datasets[0].backgroundColor[i],
                                                        strokeStyle: data.datasets[0].borderColor,
                                                        lineWidth: data.datasets[0].borderWidth,
                                                        pointStyle: 'circle',
                                                        hidden: false,
                                                        index: i
                                                    };
                                                });
                                            }
                                            return [];
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: 'white',
                                    bodyColor: 'white',
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                                            return context.label + ': ' + context.parsed + ' products (' +
                                                percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                refreshAllCharts() {
                    // Destroy existing charts
                    if (this.salesChart) this.salesChart.destroy();
                    if (this.stockChart) this.stockChart.destroy();
                    if (this.monthlyChart) this.monthlyChart.destroy();
                    if (this.categoryChart) this.categoryChart.destroy();

                    // Wait a bit then reinitialize
                    setTimeout(() => {
                        this.initCharts();
                    }, 100);
                }
            }
        }
    </script>

    {{-- Additional styling for better chart appearance --}}
    <style>
        .chart-container {
            position: relative;
            height: 100%;
            width: 100%;
        }

        canvas {
            border-radius: 8px;
        }

        .stat-card {
            transition: transform 0.2s ease-in-out;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .chart-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }

        .chart-card.dark {
            background: rgba(0, 0, 0, 0.1);
        }
    </style>
</div>
