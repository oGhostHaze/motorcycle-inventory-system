<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-mary-nav sticky class="lg:hidden">
        <x-slot:brand>
            <div class="pt-5 ml-5">
                <x-application-logo class="w-8 h-8" />
            </div>
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="mr-3 lg:hidden">
                <x-heroicon-m-bars-3 />
            </label>
        </x-slot:actions>
    </x-mary-nav>

    {{-- MAIN --}}
    <x-mary-main full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <div class="pt-5 pb-5 ml-5">
                <x-application-logo class="w-10 h-10" />
                <div class="text-lg font-bold">{{ config('app.name') }}</div>
                <div class="text-sm text-gray-500">Inventory Management</div>
            </div>

            {{-- MENU --}}
            <x-mary-menu activate-by-route>

                {{-- Dashboard --}}
                <x-mary-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard') }}" />

                {{-- Inventory Management --}}
                @if (auth()->user()->canManageInventory())
                    <x-mary-menu-sub title="Inventory" icon="o-cube">
                        <x-mary-menu-item title="Products" icon="o-squares-2x2" link="#" badge="New" />
                        <x-mary-menu-item title="Categories" icon="o-tag" link="#" />
                        <x-mary-menu-item title="Product Brands" icon="o-building-storefront" link="#" />
                        <x-mary-menu-item title="Stock Levels" icon="o-chart-bar" link="#" />
                        <x-mary-menu-item title="Stock Movements" icon="o-arrow-path" link="#" />
                        <x-mary-menu-item title="Low Stock Alerts" icon="o-exclamation-triangle" link="#"
                            badge="5" badge-classes="badge-warning" />
                        <x-mary-menu-item title="Stock Adjustments" icon="o-adjustments-horizontal" link="#" />
                    </x-mary-menu-sub>
                @endif

                {{-- Sales --}}
                @if (auth()->user()->canProcessSales())
                    <x-mary-menu-sub title="Sales" icon="o-shopping-cart">
                        <x-mary-menu-item title="Point of Sale" icon="o-calculator" link="#" badge="POS"
                            badge-classes="badge-primary" />
                        <x-mary-menu-item title="Sales History" icon="o-document-text" link="#" />
                        <x-mary-menu-item title="Customers" icon="o-users" link="#" />
                        <x-mary-menu-item title="Returns & Exchanges" icon="o-arrow-uturn-left" link="#" />
                        <x-mary-menu-item title="Promotions" icon="o-gift" link="#" />
                    </x-mary-menu-sub>
                @endif

                {{-- Purchasing --}}
                @if (auth()->user()->canManageInventory())
                    <x-mary-menu-sub title="Purchasing" icon="o-shopping-bag">
                        <x-mary-menu-item title="Purchase Orders" icon="o-document-plus" link="#" />
                        <x-mary-menu-item title="Suppliers" icon="o-truck" link="#" />
                        <x-mary-menu-item title="Receiving" icon="o-inbox-arrow-down" link="#" />
                        <x-mary-menu-item title="Auto Reorders" icon="o-arrow-path-rounded-square" link="#"
                            badge="3" badge-classes="badge-info" />
                    </x-mary-menu-sub>
                @endif

                {{-- Warehouse Management --}}
                @if (auth()->user()->canManageInventory())
                    <x-mary-menu-sub title="Warehouse" icon="o-building-office">
                        <x-mary-menu-item title="Warehouses" icon="o-map-pin" link="#" />
                        <x-mary-menu-item title="Stock Transfers" icon="o-arrow-right-circle" link="#" />
                        <x-mary-menu-item title="Cycle Counts" icon="o-clipboard-document-list" link="#" />
                        <x-mary-menu-item title="Barcode Scanner" icon="o-qr-code" link="#" badge="Scan"
                            badge-classes="badge-accent" />
                        <x-mary-menu-item title="RFID Management" icon="o-radio" link="#" />
                    </x-mary-menu-sub>
                @endif

                {{-- Reports & Analytics --}}
                @if (auth()->user()->canViewReports())
                    <x-mary-menu-sub title="Reports" icon="o-chart-pie">
                        <x-mary-menu-item title="Sales Reports" icon="o-presentation-chart-line" link="#" />
                        <x-mary-menu-item title="Inventory Reports" icon="o-chart-bar-square" link="#" />
                        <x-mary-menu-item title="Financial Reports" icon="o-currency-dollar" link="#" />
                        <x-mary-menu-item title="Profitability Analysis" icon="o-trending-up" link="#" />
                        <x-mary-menu-item title="Analytics Dashboard" icon="o-chart-pie" link="#"
                            badge="Live" badge-classes="badge-success" />
                        <x-mary-menu-item title="Export Data" icon="o-arrow-down-tray" link="#" />
                    </x-mary-menu-sub>
                @endif

                {{-- Motorcycle Database --}}
                <x-mary-menu-sub title="Motorcycles" icon="o-cog-6-tooth">
                    <x-mary-menu-item title="Motorcycle Brands" icon="o-building-storefront" link="#" />
                    <x-mary-menu-item title="Motorcycle Models" icon="o-cog" link="#" />
                    <x-mary-menu-item title="Compatibility Matrix" icon="o-puzzle-piece" link="#" />
                    <x-mary-menu-item title="Parts Lookup" icon="o-magnifying-glass" link="#" />
                </x-mary-menu-sub>

                {{-- Maintenance & Service --}}
                @if (auth()->user()->canViewReports())
                    <x-mary-menu-sub title="Service" icon="o-wrench-screwdriver">
                        <x-mary-menu-item title="Warranty Claims" icon="o-shield-check" link="#" />
                        <x-mary-menu-item title="Product Reviews" icon="o-star" link="#" />
                        <x-mary-menu-item title="Serial Numbers" icon="o-hashtag" link="#" />
                        <x-mary-menu-item title="Activity Logs" icon="o-clipboard-document" link="#" />
                        <x-mary-menu-item title="Price History" icon="o-clock" link="#" />
                    </x-mary-menu-sub>
                @endif

                <x-mary-menu-separator />

                {{-- Quick Actions --}}
                <x-mary-menu-sub title="Quick Actions" icon="o-bolt">
                    <x-mary-menu-item title="Quick Sale" icon="o-bolt" link="#" badge="Fast"
                        badge-classes="badge-warning" />
                    <x-mary-menu-item title="Product Lookup" icon="o-magnifying-glass-circle" link="#" />
                    <x-mary-menu-item title="Stock Check" icon="o-eye" link="#" />
                    <x-mary-menu-item title="Print Labels" icon="o-printer" link="#" />
                </x-mary-menu-sub>

                {{-- System Administration --}}
                @if (auth()->user()->canManageUsers())
                    <x-mary-menu-sub title="Administration" icon="o-cog-8-tooth">
                        <x-mary-menu-item title="Users" icon="o-user-group" link="#" />
                        <x-mary-menu-item title="Roles & Permissions" icon="o-key" link="#" />
                        <x-mary-menu-item title="System Settings" icon="o-adjustments-horizontal" link="#" />
                        <x-mary-menu-item title="Business Settings" icon="o-building-office-2" link="#" />
                        <x-mary-menu-item title="Tax Settings" icon="o-calculator" link="#" />
                        <x-mary-menu-item title="Backup & Restore" icon="o-server" link="#" />
                        <x-mary-menu-item title="API Management" icon="o-code-bracket" link="#" />
                    </x-mary-menu-sub>
                @endif

                <x-mary-menu-separator />

                {{-- User Account --}}
                <x-mary-menu-item title="Profile" icon="o-user" link="{{ route('profile.show') }}" />
                <x-mary-menu-item title="Notifications" icon="o-bell" link="#" badge="12"
                    badge-classes="badge-error" />

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-mary-menu-item title="Logout" icon="o-power"
                        onclick="event.preventDefault(); this.closest('form').submit();" />
                </form>

            </x-mary-menu>

            {{-- User Info at Bottom --}}
            <div class="p-4 mt-auto border-t border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="avatar">
                        <div
                            class="flex items-center justify-center w-8 h-8 text-sm font-bold rounded-full bg-primary text-primary-content">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 truncate">
                            {{ auth()->user()->name }}
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            {{ ucfirst(auth()->user()->role) }}
                        </div>
                    </div>
                </div>
            </div>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-mary-main>

    {{--  TOAST area --}}
    <x-mary-toast />

    {{-- Status Bar (Optional) --}}
    <div class="fixed bottom-0 right-0 z-50 p-4">
        <div class="flex items-center p-3 space-x-2 text-xs rounded-lg shadow-lg bg-base-100">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <span>System Online</span>
            <span class="text-gray-500">|</span>
            <span>{{ now()->format('M d, Y H:i') }}</span>
        </div>
    </div>
    @livewireScripts
    @stack('scripts')
</body>

</html>
