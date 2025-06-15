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

    {{-- Key Performance Stats - Enhanced with Profit Metrics --}}
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
        {{-- Today's Sales --}}
        <x-mary-stat title="Today's Sales" description="Revenue today" value="₱{{ number_format($todaysSales, 2) }}"
            icon="o-currency-dollar" color="text-primary"
            class="shadow-lg bg-gradient-to-r from-primary/10 to-primary/5">
            <x-slot:actions>
                <div class="text-xs {{ $monthlyGrowth >= 0 ? 'text-success' : 'text-error' }}">
                    {{ $monthlyGrowth >= 0 ? '+' : '' }}{{ number_format($monthlyGrowth, 1) }}% vs last month
                </div>
            </x-slot:actions>
        </x-mary-stat>

        {{-- NEW: Today's Profit --}}
        <x-mary-stat title="Today's Profit" description="Net profit today"
            value="₱{{ number_format($todaysProfit, 2) }}" icon="o-chart-bar" color="text-success"
            class="shadow-lg bg-gradient-to-r from-success/10 to-success/5">
            <x-slot:actions>
                <div class="text-xs text-success">
                    {{ number_format($todaysProfitMargin, 1) }}% margin
                </div>
            </x-slot:actions>
        </x-mary-stat>

        {{-- Month Sales --}}
        <x-mary-stat title="Month Sales" description="Total this month" value="₱{{ number_format($monthSales, 2) }}"
            icon="o-calendar" color="text-info" class="shadow-lg bg-gradient-to-r from-info/10 to-info/5">
            <x-slot:actions>
                <div class="text-xs text-info">
                    {{ now()->format('M Y') }}
                </div>
            </x-slot:actions>
        </x-mary-stat>

        {{-- NEW: Month Profit --}}
        <x-mary-stat title="Month Profit" description="Net profit this month"
            value="₱{{ number_format($monthProfit, 2) }}" icon="o-arrow-trending-up" color="text-success"
            class="shadow-lg bg-gradient-to-r from-success/10 to-success/5">
            <x-slot:actions>
                <div class="text-xs text-success">
                    {{ number_format($monthProfitMargin, 1) }}% margin
                </div>
            </x-slot:actions>
        </x-mary-stat>

        {{-- Low Stock Items --}}
        <x-mary-stat title="Low Stock Items" description="Require attention" value="{{ $lowStockItems }}"
            icon="o-exclamation-triangle" color="text-warning"
            class="shadow-lg bg-gradient-to-r from-warning/10 to-warning/5">
            <x-slot:actions>
                <x-mary-button label="View" link="{{ route('inventory.low-stock-alerts') }}" size="sm"
                    class="btn-warning btn-xs" />
            </x-slot:actions>
        </x-mary-stat>

        {{-- Pending Orders --}}
        <x-mary-stat title="Pending Orders" description="Awaiting processing" value="{{ $pendingOrders }}"
            icon="o-clipboard-document-list" color="text-secondary"
            class="shadow-lg bg-gradient-to-r from-secondary/10 to-secondary/5">
            <x-slot:actions>
                <x-mary-button label="View" link="{{ route('purchasing.purchase-orders') }}" size="sm"
                    class="btn-secondary btn-xs" />
            </x-slot:actions>
        </x-mary-stat>
    </div>

    {{-- NEW: Profit Analytics Row --}}
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
        {{-- Year Profit Overview --}}
        <x-mary-card title="Year Profit Overview" subtitle="{{ now()->format('Y') }} Performance" class="shadow-lg">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Profit:</span>
                    <span class="text-lg font-bold text-success">₱{{ number_format($yearProfit, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Revenue:</span>
                    <span class="text-md font-semibold">₱{{ number_format($yearSales, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">COGS:</span>
                    <span class="text-md">₱{{ number_format($totalCostOfGoodsSold, 2) }}</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t">
                    <span class="text-sm font-semibold">Profit Margin:</span>
                    <span
                        class="text-lg font-bold {{ $yearProfitMargin >= 30 ? 'text-success' : ($yearProfitMargin >= 15 ? 'text-warning' : 'text-error') }}">
                        {{ number_format($yearProfitMargin, 1) }}%
                    </span>
                </div>
            </div>
        </x-mary-card>

        {{-- Average Transaction Metrics --}}
        <x-mary-card title="Transaction Metrics" subtitle="Average per sale" class="shadow-lg">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Avg. Sale Value:</span>
                    <span
                        class="text-md font-semibold">₱{{ number_format($yearSales > 0 && \App\Models\Sale::whereYear('created_at', now()->year)->where('status', 'completed')->count() > 0 ? $yearSales / \App\Models\Sale::whereYear('created_at', now()->year)->where('status', 'completed')->count() : 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Avg. Profit:</span>
                    <span
                        class="text-md font-bold text-success">₱{{ number_format($averageTransactionProfit, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Transactions:</span>
                    <span
                        class="text-md">{{ \App\Models\Sale::whereYear('created_at', now()->year)->where('status', 'completed')->count() }}</span>
                </div>
            </div>
        </x-mary-card>

        {{-- Inventory Value --}}
        <x-mary-card title="Inventory Overview" subtitle="Current stock value" class="shadow-lg">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Products:</span>
                    <span class="text-md font-semibold">{{ number_format($totalProducts) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Inventory Value:</span>
                    <span class="text-lg font-bold text-info">₱{{ number_format($totalInventoryValue, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Active Suppliers:</span>
                    <span class="text-md">{{ $totalSuppliers }}</span>
                </div>
            </div>
        </x-mary-card>

        {{-- Quick Actions --}}
        <x-mary-card title="Quick Actions" subtitle="Common tasks" class="shadow-lg">
            <div class="space-y-2">
                <x-mary-button label="New Sale" icon="o-plus" link="{{ route('sales.pos') }}"
                    class="btn-primary btn-sm w-full" />
                <x-mary-button label="Add Product" icon="o-cube" link="{{ route('inventory.products') }}"
                    class="btn-outline btn-sm w-full" />
                <x-mary-button label="Purchase Order" icon="o-clipboard-document-list"
                    link="{{ route('purchasing.purchase-orders') }}" class="btn-outline btn-sm w-full" />
                <x-mary-button label="Stock Adjustment" icon="o-adjustments-horizontal"
                    link="{{ route('inventory.stock-adjustments') }}" class="btn-outline btn-sm w-full" />
            </div>
        </x-mary-card>
    </div>

    {{-- Alerts --}}
    @if ($lowStockItems > 0 || $pendingOrders > 0)
        <div class="mb-8 space-y-4">
            @if ($lowStockItems > 0)
                <x-mary-alert title="Low Stock Alert"
                    description="{{ $lowStockItems }} items are running low on stock" icon="o-exclamation-triangle"
                    class="shadow-lg alert-warning">
                    <x-slot:actions>
                        <x-mary-button label="View Items" link="{{ route('inventory.low-stock-alerts') }}"
                            size="sm" class="btn-warning" />
                    </x-slot:actions>
                </x-mary-alert>
            @endif

            @if ($pendingOrders > 0)
                <x-mary-alert title="Pending Orders"
                    description="{{ $pendingOrders }} purchase orders need attention" icon="o-document-text"
                    class="shadow-lg alert-info">
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
        {{-- Sales & Profit Trend Chart --}}
        <div class="lg:col-span-8">
            <x-mary-card title="Sales & Profit Trend (Last 30 Days)" subtitle="Daily performance comparison"
                class="h-96">
                <div class="p-4 h-80">
                    <canvas id="salesProfitChart" class="w-full h-full"></canvas>
                </div>
                <x-slot:actions>
                    <x-mary-button icon="o-arrow-top-right-on-square" class="btn-ghost btn-sm" />
                </x-slot:actions>
            </x-mary-card>
        </div>

        {{-- Profit by Category Chart --}}
        <div class="lg:col-span-4">
            <x-mary-card title="Profit by Category" subtitle="This month's breakdown" class="h-96">
                <div class="p-4 h-80">
                    <canvas id="profitCategoryChart" class="w-full h-full"></canvas>
                </div>
            </x-mary-card>
        </div>
    </div>

    {{-- Data Tables Section --}}
    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-12">
        {{-- Top Profit Products --}}
        <div class="lg:col-span-6">
            <x-mary-card title="Top Profit Products" subtitle="This month's most profitable items" class="shadow-lg">
                @if (count($topProfitProducts) > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sold</th>
                                    <th>Profit</th>
                                    <th>Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topProfitProducts as $product)
                                    <tr>
                                        <td>
                                            <div>
                                                <div class="font-bold">{{ $product->name }}</div>
                                                <div class="text-sm opacity-50">{{ $product->sku }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-outline">{{ number_format($product->total_sold ?? 0) }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="font-bold text-success">₱{{ number_format($product->total_profit ?? 0, 2) }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $product->profit_margin >= 30 ? 'badge-success' : ($product->profit_margin >= 15 ? 'badge-warning' : 'badge-error') }}">
                                                {{ number_format($product->profit_margin, 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-chart-bar class="w-12 h-12 mx-auto text-gray-400" />
                        <p class="mt-2 text-gray-500">No profit data available</p>
                    </div>
                @endif
                <x-slot:actions>
                    <x-mary-button label="View All" link="{{ route('inventory.products') }}" size="sm"
                        class="btn-outline" />
                </x-slot:actions>
            </x-mary-card>
        </div>

        {{-- Recent Sales --}}
        <div class="lg:col-span-6">
            <x-mary-card title="Recent Sales" subtitle="Latest transactions" class="shadow-lg">
                @if (count($recentSales) > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentSales as $sale)
                                    <tr>
                                        <td>
                                            <span class="font-mono text-sm">{{ $sale->invoice_number }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="font-bold">{{ $sale->customer->name ?? 'Walk-in' }}</div>
                                                <div class="text-sm opacity-50">{{ $sale->user->name }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="font-bold">₱{{ number_format($sale->total_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-sm">{{ $sale->created_at->diffForHumans() }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-shopping-cart class="w-12 h-12 mx-auto text-gray-400" />
                        <p class="mt-2 text-gray-500">No recent sales</p>
                    </div>
                @endif
                <x-slot:actions>
                    <x-mary-button label="View All" link="{{ route('sales.history') }}" size="sm"
                        class="btn-outline" />
                </x-slot:actions>
            </x-mary-card>
        </div>
    </div>

    {{-- Additional Analytics Row --}}
    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-12">
        {{-- Top Customers --}}
        <div class="lg:col-span-4">
            <x-mary-card title="Top Customers" subtitle="This month's best customers" class="shadow-lg">
                @if (count($topCustomers) > 0)
                    <div class="space-y-3">
                        @foreach ($topCustomers as $customer)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-base-200">
                                <div>
                                    <div class="font-bold">{{ $customer->name }}</div>
                                    <div class="text-sm opacity-70">{{ $customer->total_orders }} orders</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-primary">
                                        ₱{{ number_format($customer->total_spent, 2) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-users class="w-12 h-12 mx-auto text-gray-400" />
                        <p class="mt-2 text-gray-500">No customer data</p>
                    </div>
                @endif
                <x-slot:actions>
                    <x-mary-button label="View All" link="{{ route('sales.customers') }}" size="sm"
                        class="btn-outline" />
                </x-slot:actions>
            </x-mary-card>
        </div>

        {{-- Stock Status Distribution --}}
        <div class="lg:col-span-4">
            <x-mary-card title="Stock Status" subtitle="Inventory distribution" class="shadow-lg">
                <div class="p-4">
                    <canvas id="stockStatusChart" class="w-full h-48"></canvas>
                </div>
            </x-mary-card>
        </div>

        {{-- Low Stock Products --}}
        <div class="lg:col-span-4">
            <x-mary-card title="Low Stock Alert" subtitle="Items requiring attention" class="shadow-lg">
                @if (count($lowStockProducts) > 0)
                    <div class="space-y-2">
                        @foreach ($lowStockProducts as $product)
                            <div class="flex items-center justify-between p-2 rounded bg-warning/10">
                                <div>
                                    <div class="font-bold text-sm">{{ $product->name }}</div>
                                    <div class="text-xs opacity-70">{{ $product->category->name ?? 'No Category' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-warning">{{ $product->current_stock }}</div>
                                    <div class="text-xs opacity-70">Min: {{ $product->min_stock_level }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-check-circle class="w-12 h-12 mx-auto text-success" />
                        <p class="mt-2 text-success">All products well stocked!</p>
                    </div>
                @endif
                <x-slot:actions>
                    <x-mary-button label="View All" link="{{ route('inventory.low-stock-alerts') }}" size="sm"
                        class="btn-warning" />
                </x-slot:actions>
            </x-mary-card>
        </div>
    </div>

    {{-- Load Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    {{-- Charts JavaScript --}}
    <script>
        function dashboardCharts() {
            return {
                salesProfitChart: null,
                profitCategoryChart: null,
                stockStatusChart: null,

                initCharts() {
                    this.initSalesProfitChart();
                    this.initProfitCategoryChart();
                    this.initStockStatusChart();

                    // Listen for Livewire events - Proper way to handle Livewire events
                    document.addEventListener('livewire:initialized', () => {
                        this.$wire.on('refresh-charts', () => {
                            this.refreshAllCharts();
                        });
                    });
                },

                initSalesProfitChart() {
                    const ctx = document.getElementById('salesProfitChart').getContext('2d');
                    const profitData = @js($profitTrend);

                    this.salesProfitChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: profitData.map(item => item.date),
                            datasets: [{
                                label: 'Revenue',
                                data: profitData.map(item => item.revenue),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1,
                                yAxisID: 'y'
                            }, {
                                label: 'Profit',
                                data: profitData.map(item => item.profit),
                                borderColor: 'rgb(16, 185, 129)',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1,
                                yAxisID: 'y'
                            }, {
                                label: 'Profit Margin (%)',
                                data: profitData.map(item => item.margin),
                                borderColor: 'rgb(245, 158, 11)',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1,
                                yAxisID: 'y1',
                                type: 'line'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            scales: {
                                x: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Date'
                                    }
                                },
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Amount (₱)'
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return '₱' + value.toLocaleString();
                                        }
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Margin (%)'
                                    },
                                    grid: {
                                        drawOnChartArea: false,
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: 'white',
                                    bodyColor: 'white',
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.dataset.yAxisID === 'y1') {
                                                label += context.parsed.y.toFixed(1) + '%';
                                            } else {
                                                label += '₱' + context.parsed.y.toLocaleString();
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                initProfitCategoryChart() {
                    const ctx = document.getElementById('profitCategoryChart').getContext('2d');
                    const categoryData = @js($profitByCategory);

                    this.profitCategoryChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: categoryData.map(item => item.name),
                            datasets: [{
                                data: categoryData.map(item => item.total_profit),
                                backgroundColor: [
                                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
                                    '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'
                                ],
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
                                        padding: 15,
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        font: {
                                            size: 11
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
                                            return context.label + ': ₱' + context.parsed.toLocaleString() +
                                                ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                initStockStatusChart() {
                    const ctx = document.getElementById('stockStatusChart').getContext('2d');
                    const stockData = @js($stockStatusData);

                    this.stockStatusChart = new Chart(ctx, {
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
                                        padding: 15,
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        font: {
                                            size: 11
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
                                            return context.label + ': ' + context.parsed + ' (' + percentage +
                                                '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                refreshAllCharts() {
                    // Destroy existing charts
                    if (this.salesProfitChart) {
                        this.salesProfitChart.destroy();
                        this.salesProfitChart = null;
                    }
                    if (this.profitCategoryChart) {
                        this.profitCategoryChart.destroy();
                        this.profitCategoryChart = null;
                    }
                    if (this.stockStatusChart) {
                        this.stockStatusChart.destroy();
                        this.stockStatusChart = null;
                    }

                    // Wait a bit then reinitialize with fresh data
                    setTimeout(() => {
                        this.initCharts();
                    }, 100);
                }
            }
        }
    </script>

</div>
