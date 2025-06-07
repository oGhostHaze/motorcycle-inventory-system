<div>
    {{-- Page Header --}}
    <x-mary-header title="Dashboard" subtitle="Welcome back, {{ auth()->user()->name }}!" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-path" wire:click="refreshData" class="btn-ghost" tooltip="Refresh Data" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
        {{-- Today's Sales --}}
        <x-mary-stat title="Today's Sales" description="Total sales today" value="₱{{ $todaysSales }}"
            icon="o-currency-dollar" color="text-primary" class="bg-gradient-to-r from-primary/10 to-primary/5" />

        {{-- Monthly Sales --}}
        <x-mary-stat title="Monthly Sales" description="Sales this month" value="₱{{ $monthSales }}"
            icon="o-chart-bar" color="text-success" class="bg-gradient-to-r from-success/10 to-success/5" />

        {{-- Total Products --}}
        <x-mary-stat title="Total Products" description="Active products" value="{{ number_format($totalProducts) }}"
            icon="o-cube" color="text-info" class="bg-gradient-to-r from-info/10 to-info/5" />

        {{-- Inventory Value --}}
        <x-mary-stat title="Inventory Value" description="Total stock worth" value="₱{{ $totalInventoryValue }}"
            icon="o-banknotes" color="text-warning" class="bg-gradient-to-r from-warning/10 to-warning/5" />
    </div>

    {{-- Alert Cards --}}
    @if ($lowStockItems > 0 || $pendingOrders > 0)
        <div class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-2">
            @if ($lowStockItems > 0)
                <x-mary-alert title="Low Stock Alert"
                    description="{{ $lowStockItems }} products are running low on stock" icon="o-exclamation-triangle"
                    class="alert-warning">
                    <x-slot:actions>
                        <x-mary-button label="View Items" link="#" size="sm" class="btn-warning" />
                    </x-slot:actions>
                </x-mary-alert>
            @endif

            @if ($pendingOrders > 0)
                <x-mary-alert title="Pending Orders" description="{{ $pendingOrders }} purchase orders need attention"
                    icon="o-document-text" class="alert-info">
                    <x-slot:actions>
                        <x-mary-button label="View Orders" link="#" size="sm" class="btn-info" />
                    </x-slot:actions>
                </x-mary-alert>
            @endif
        </div>
    @endif

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

        {{-- Sales Chart --}}
        <div class="lg:col-span-8">
            <x-mary-card title="Sales Trend (Last 7 Days)" subtitle="Daily sales performance">
                <div class="h-64 p-4">
                    {{-- Placeholder for chart - you can integrate Chart.js or similar --}}
                    <div
                        class="flex items-center justify-center h-full border-2 border-gray-300 border-dashed rounded-lg">
                        <div class="text-center">
                            <x-heroicon-o-chart-bar class="w-12 h-12 mx-auto text-gray-400" />
                            <p class="mt-2 text-gray-500">Sales Chart</p>
                            <p class="text-sm text-gray-400">Integration pending</p>
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>

        {{-- Quick Actions --}}
        <div class="lg:col-span-4">
            <x-mary-card title="Quick Actions" subtitle="Common tasks">
                <div class="space-y-3">
                    <x-mary-button label="Process Sale" icon="o-shopping-cart" link="#"
                        class="w-full btn-primary" />
                    <x-mary-button label="Add Product" icon="o-plus" link="#" class="w-full btn-secondary" />
                    <x-mary-button label="Stock Adjustment" icon="o-adjustments-horizontal" link="#"
                        class="w-full btn-accent" />
                    <x-mary-button label="Create PO" icon="o-document-plus" link="#" class="w-full btn-info" />
                    <x-mary-button label="Barcode Scanner" icon="o-qr-code" link="#" class="w-full btn-warning" />
                </div>
            </x-mary-card>
        </div>

        {{-- Recent Sales --}}
        <div class="lg:col-span-6">
            <x-mary-card title="Recent Sales" subtitle="Latest transactions">
                <div class="space-y-3">
                    @forelse($recentSales as $sale)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-base-200">
                            <div>
                                <div class="font-medium">{{ $sale->invoice_number }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ $movement->type }} • {{ $movement->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div
                                    class="font-bold {{ $movement->quantity_changed > 0 ? 'text-success' : 'text-error' }}">
                                    {{ $movement->quantity_changed > 0 ? '+' : '' }}{{ $movement->quantity_changed }}
                                </div>
                                <div class="text-sm text-gray-500">{{ $movement->warehouse->name }}</div>
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
    </div>

    {{-- System Status Footer --}}
    <div class="mt-8">
        <x-mary-card>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm">System Online</span>
                    </div>
                    <div class="text-sm text-gray-500">
                        Last updated: {{ now()->format('M d, Y H:i') }}
                    </div>
                </div>
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                    <span>{{ $totalCustomers }} Customers</span>
                    <span>•</span>
                    <span>{{ $totalProducts }} Products</span>
                    <span>•</span>
                    <span>Version 1.0</span>
                </div>
            </div>
        </x-mary-card>
    </div>
</div>
