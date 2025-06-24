<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased">
    <x-banner />

    {{-- Top Navigation --}}
    <x-mary-nav sticky>
        {{-- Left side - Breadcrumbs or title --}}

        <x-slot:brand>
            <div class="flex gap-4">
                <x-application-logo class="w-6 h-6" />
                <span class="mr-3 text-lg font-semibold">{{ config('app.name') }}</span>
            </div>
        </x-slot:brand>

        {{-- Right side - Theme switcher, notifications, search --}}

        <x-slot:actions>
            <div class="flex items-center gap-2">
                {{-- Notifications --}}
                <x-mary-dropdown>
                    <x-slot:trigger>
                        <x-mary-button icon="o-bell" class="btn-ghost btn-sm" tooltip-bottom="Notifications">
                            <x-mary-badge value="3" class="absolute badge-error badge-xs -top-1 -right-1" />
                        </x-mary-button>
                    </x-slot:trigger>

                    <div class="p-4 w-80">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-sm font-semibold">Notifications</div>
                            <x-mary-badge value="3 new" class="badge-error badge-xs" />
                        </div>
                        <div class="space-y-3 overflow-y-auto max-h-64">
                            <div class="p-3 border rounded-lg bg-warning/10 border-warning/20">
                                <div class="text-sm font-medium text-warning-700">Low Stock Alert</div>
                                <div class="text-xs text-gray-600">5 products are running low</div>
                                <div class="mt-1 text-xs text-gray-400">2 minutes ago</div>
                            </div>
                            <div class="p-3 border rounded-lg bg-info/10 border-info/20">
                                <div class="text-sm font-medium text-info-700">New Order</div>
                                <div class="text-xs text-gray-600">Purchase order #PO-001 received</div>
                                <div class="mt-1 text-xs text-gray-400">1 hour ago</div>
                            </div>
                            <div class="p-3 border rounded-lg bg-success/10 border-success/20">
                                <div class="text-sm font-medium text-success-700">Sale Completed</div>
                                <div class="text-xs text-gray-600">₱2,450 sale processed</div>
                                <div class="mt-1 text-xs text-gray-400">3 hours ago</div>
                            </div>
                        </div>
                        <div class="pt-3 mt-3 border-t">
                            <a href="#" class="text-xs text-primary hover:underline">View all notifications</a>
                        </div>
                    </div>
                </x-mary-dropdown>

                <x-mary-theme-toggle class="btn btn-circle btn-ghost" />
                {{-- Quick Actions --}}
                <x-mary-dropdown>
                    <x-slot:trigger>
                        <x-mary-button icon="o-plus" class="btn-primary btn-sm" tooltip-bottom="Quick Actions" />
                    </x-slot:trigger>

                    <div class="w-48 p-3">
                        <div class="mb-3 text-sm font-semibold">Quick Actions</div>
                        <div class="space-y-2">
                            <a href="{{ route('sales.pos') }}"
                                class="flex items-center gap-2 p-2 transition-colors rounded hover:bg-base-200">
                                <x-mary-icon name="o-shopping-cart" class="w-4 h-4" />
                                <span class="text-sm">New Sale</span>
                            </a>
                            <a href="{{ route('inventory.products') }}"
                                class="flex items-center gap-2 p-2 transition-colors rounded hover:bg-base-200">
                                <x-mary-icon name="o-plus" class="w-4 h-4" />
                                <span class="text-sm">Add Product</span>
                            </a>
                            <a href="{{ route('inventory.stock-adjustments') }}"
                                class="flex items-center gap-2 p-2 transition-colors rounded hover:bg-base-200">
                                <x-mary-icon name="o-adjustments-horizontal" class="w-4 h-4" />
                                <span class="text-sm">Stock Adjustment</span>
                            </a>
                            <a href="{{ route('purchasing.purchase-orders') }}"
                                class="flex items-center gap-2 p-2 transition-colors rounded hover:bg-base-200">
                                <x-mary-icon name="o-document-plus" class="w-4 h-4" />
                                <span class="text-sm">New PO</span>
                            </a>
                        </div>
                    </div>
                </x-mary-dropdown>
            </div>
        </x-slot:actions>
    </x-mary-nav>
    {{-- Use MaryUI Main Layout --}}
    <x-mary-main with-nav>

        {{-- Sidebar --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-200 lg:bg-inherit">
            {{-- User Info --}}
            <x-mary-list-item :item="auth()->user()" value="name" sub-value="email" no-separator no-hover
                class="-mx-2 !-my-2 rounded">
                <x-slot:actions>
                    <x-mary-dropdown>
                        <x-slot:trigger>
                            <x-mary-button icon="o-cog-6-tooth" class="btn-ghost btn-sm" />
                        </x-slot:trigger>

                        <x-mary-menu-item title="Profile" icon="o-user" link="{{ route('profile.show') }}" />

                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-mary-menu-item title="API Tokens" icon="o-key"
                                link="{{ route('api-tokens.index') }}" />
                        @endif

                        <x-mary-menu-separator />

                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <x-mary-menu-item title="Logout" icon="o-power" x-on:click.prevent="$root.submit();" />
                        </form>
                    </x-mary-dropdown>
                </x-slot:actions>
            </x-mary-list-item>

            {{-- Navigation Menu --}}
            <x-mary-menu activate-by-route>
                {{-- Dashboard --}}
                <x-mary-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard') }}" />

                <x-mary-menu-separator />

                {{-- Check if user is admin - if yes, show everything --}}
                @php
                    $user = auth()->user();
                    $isAdmin =
                        $user->role === 'admin' ||
                        $user->email === 'admin@motoshop.com' ||
                        (method_exists($user, 'isAdmin') && $user->isAdmin()) ||
                        $user->id === 1; // First user is usually admin

                    $isManager =
                        $isAdmin ||
                        $user->role === 'manager' ||
                        (method_exists($user, 'isManager') && $user->isManager());

                    $isCashier =
                        $isAdmin ||
                        $isManager ||
                        $user->role === 'cashier' ||
                        (method_exists($user, 'isCashier') && $user->isCashier());

                    $isWarehouseStaff =
                        $isAdmin ||
                        $user->role === 'warehouse_staff' ||
                        (method_exists($user, 'isWarehouseStaff') && $user->isWarehouseStaff());
                @endphp

                {{-- Inventory Management - Admin, Manager, or Warehouse Staff --}}
                @if ($isAdmin || $isManager || $isWarehouseStaff)
                    <x-mary-menu-sub title="Inventory" icon="o-cube">
                        <x-mary-menu-item title="Products" icon="o-cube" link="{{ route('inventory.products') }}" />
                        <x-mary-menu-item title="Categories" icon="o-tag"
                            link="{{ route('inventory.categories') }}" />
                        <x-mary-menu-item title="Stock Levels" icon="o-chart-bar"
                            link="{{ route('inventory.stock-levels') }}" />
                        <x-mary-menu-item title="Stock Movements" icon="o-arrow-path"
                            link="{{ route('inventory.stock-movements') }}" />
                        <x-mary-menu-item title="Stock Adjustments" icon="o-adjustments-horizontal"
                            link="{{ route('inventory.stock-adjustments') }}" />
                        <x-mary-menu-item title="Low Stock Alerts" icon="o-exclamation-triangle"
                            link="{{ route('inventory.low-stock-alerts') }}" />
                        <x-mary-menu-item title="Warehouses" icon="o-building-office"
                            link="{{ route('inventory.warehouses') }}" />
                        <x-mary-menu-item title="Inventory Locations" icon="o-map-pin"
                            link="{{ route('inventory.locations') }}" />
                    </x-mary-menu-sub>
                @endif

                {{-- Sales - Admin, Manager, or Cashier --}}
                @if ($isAdmin || $isManager || $isCashier)
                    <x-mary-menu-sub title="Sales" icon="o-shopping-cart">
                        <x-mary-menu-item title="Point of Sale" icon="o-shopping-cart"
                            link="{{ route('sales.pos') }}" />
                        <x-mary-menu-item title="Sales History" icon="o-document-text"
                            link="{{ route('sales.history') }}" />
                        <x-mary-menu-item title="Returns & Exchanges" icon="o-arrow-uturn-left"
                            link="{{ route('sales.returns') }}" />
                        <x-mary-menu-item title="Warranty Tracking" icon="o-shield-check"
                            link="{{ route('admin.warranty-tracking') }}" />
                        <x-mary-menu-item title="Shift Management" icon="o-clock"
                            link="{{ route('sales.shifts') }}" />
                        <x-mary-menu-item title="Customers" icon="o-users" link="{{ route('sales.customers') }}" />
                    </x-mary-menu-sub>
                @endif

                {{-- Purchasing - Admin, Manager, or Warehouse Staff --}}
                @if ($isAdmin || $isManager || $isWarehouseStaff)
                    <x-mary-menu-sub title="Purchasing" icon="o-truck">
                        <x-mary-menu-item title="Purchase Orders" icon="o-document-text"
                            link="{{ route('purchasing.purchase-orders') }}" />
                        <x-mary-menu-item title="Suppliers" icon="o-building-office-2"
                            link="{{ route('purchasing.suppliers') }}" />
                    </x-mary-menu-sub>
                @endif

                <x-mary-menu-separator />

                {{-- Reports - Admin or Manager --}}
                @if ($isAdmin || $isManager)
                    <x-mary-menu-sub title="Reports" icon="o-chart-pie">
                        <x-mary-menu-item title="Sales Reports" icon="o-chart-bar"
                            link="{{ route('reports.sales') }}" />
                        <x-mary-menu-item title="Inventory Reports" icon="o-cube"
                            link="{{ route('reports.inventory') }}" />
                        <x-mary-menu-item title="Financial Reports" icon="o-banknotes"
                            link="{{ route('reports.financial') }}" />
                        <x-mary-menu-item title="Customer Reports" icon="o-users"
                            link="{{ route('reports.customers') }}" />
                    </x-mary-menu-sub>
                @endif

                @if ($isAdmin)
                    <x-mary-menu-separator />
                    <x-mary-menu-sub title="Administration" icon="o-cog-6-tooth">
                        <x-mary-menu-item title="User Management" icon="o-users"
                            link="{{ route('admin.users') }}" />
                        <x-mary-menu-item title="Recompute Totals" icon="o-calculator"
                            link="{{ route('admin.recompute') }}" />
                        <x-mary-menu-item title="System Settings" icon="o-adjustments-horizontal"
                            link="{{ route('admin.settings') }}" />
                        <x-mary-menu-item title="Activity Logs" icon="o-clipboard-document-list"
                            link="{{ route('admin.activity-logs') }}" />
                        <x-mary-menu-item title="Database Backup" icon="o-server"
                            link="{{ route('admin.backup') }}" />
                    </x-mary-menu-sub>
                @endif

                {{-- System Info (for debugging - remove in production) --}}
                @if ($isAdmin)
                    <x-mary-menu-separator />
                    <div class="px-4 py-2 text-xs text-gray-500">
                        <div>User ID: {{ $user->id }}</div>
                        <div>Role: {{ $user->role ?? 'none' }}</div>
                        <div>Admin: {{ $isAdmin ? 'Yes' : 'No' }}</div>
                    </div>
                @endif
            </x-mary-menu>
        </x-slot:sidebar>

        {{-- Content --}}
        <x-slot:content class="p-6">
            {{ $slot }}
        </x-slot:content>
    </x-mary-main>

    @stack('scripts')
    @stack('modals')
    @livewireScripts
    <x-mary-toast />
</body>

</html>
