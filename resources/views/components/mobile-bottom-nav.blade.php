<div class="fixed bottom-0 left-0 right-0 z-30 border-t lg:hidden bg-base-100 border-base-300">
    <div class="grid h-16 grid-cols-5">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
            class="flex flex-col items-center justify-center text-xs {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-base-content/60' }}">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            <span>Home</span>
        </a>

        {{-- Point of Sale --}}
        <a href="{{ route('sales.pos') }}"
            class="flex flex-col items-center justify-center text-xs {{ request()->routeIs('sales.pos*') ? 'text-primary' : 'text-base-content/60' }}">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6">
                </path>
            </svg>
            <span>POS</span>
        </a>

        {{-- Inventory (with dropdown trigger) --}}
        <button onclick="toggleInventoryMenu()"
            class="flex flex-col items-center justify-center text-xs {{ request()->routeIs('inventory.*') ? 'text-primary' : 'text-base-content/60' }}">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <span>Inventory</span>
        </button>

        {{-- Sales --}}
        <a href="{{ route('sales.history') }}"
            class="flex flex-col items-center justify-center text-xs {{ request()->routeIs('sales.history*') || request()->routeIs('sales.reports*') ? 'text-primary' : 'text-base-content/60' }}">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                </path>
            </svg>
            <span>Sales</span>
        </a>

        {{-- More (with menu trigger) --}}
        <button onclick="toggleMoreMenu()"
            class="flex flex-col items-center justify-center text-xs text-base-content/60">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z">
                </path>
            </svg>
            <span>More</span>
        </button>
    </div>

    {{-- Inventory Quick Menu Popup --}}
    <div id="inventory-menu"
        class="absolute left-0 right-0 hidden border-t shadow-lg bottom-16 bg-base-100 border-base-300">

        <div class="grid grid-cols-3 gap-2 p-4">
            <a href="{{ route('inventory.products') }}"
                class="flex flex-col items-center p-3 rounded-lg hover:bg-base-200 {{ request()->routeIs('inventory.products*') ? 'bg-primary text-primary-content' : '' }}"
                onclick="hideInventoryMenu()">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <span class="text-xs">Products</span>
            </a>

            <a href="{{ route('inventory.services') }}"
                class="flex flex-col items-center p-3 rounded-lg hover:bg-base-200 {{ request()->routeIs('inventory.services*') ? 'bg-primary text-primary-content' : '' }}"
                onclick="hideInventoryMenu()">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-xs">Services</span>
            </a>

            <a href="{{ route('inventory.categories') }}"
                class="flex flex-col items-center p-3 rounded-lg hover:bg-base-200 {{ request()->routeIs('inventory.categories*') ? 'bg-primary text-primary-content' : '' }}"
                onclick="hideInventoryMenu()">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                    </path>
                </svg>
                <span class="text-xs">Categories</span>
            </a>

            <a href="{{ route('inventory.stock-movements') }}"
                class="flex flex-col items-center p-3 rounded-lg hover:bg-base-200 {{ request()->routeIs('inventory.stock-movements*') ? 'bg-primary text-primary-content' : '' }}"
                onclick="hideInventoryMenu()">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span class="text-xs">Movements</span>
            </a>

            <a href="{{ route('inventory.stock-adjustments') }}"
                class="flex flex-col items-center p-3 rounded-lg hover:bg-base-200 {{ request()->routeIs('inventory.stock-adjustments*') ? 'bg-primary text-primary-content' : '' }}"
                onclick="hideInventoryMenu()">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
                    </path>
                </svg>
                <span class="text-xs">Adjustments</span>
            </a>

            <a href="{{ route('inventory.locations') }}"
                class="flex flex-col items-center p-3 rounded-lg hover:bg-base-200 {{ request()->routeIs('inventory.locations*') ? 'bg-primary text-primary-content' : '' }}"
                onclick="hideInventoryMenu()">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-xs">Locations</span>
            </a>
        </div>
    </div>

    {{-- More Menu Popup --}}
    <div id="more-menu"
        class="absolute left-0 right-0 hidden border-t shadow-lg bottom-16 bg-base-100 border-base-300">

        <div class="p-4 space-y-2">
            <a href="{{ route('sales.customers') }}"
                class="flex items-center gap-3 p-3 rounded-lg hover:bg-base-200 {{ request()->routeIs('sales.customers*') ? 'bg-primary text-primary-content' : '' }}"
                onclick="hideMoreMenu()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                    </path>
                </svg>
                <span>Customers</span>
            </a>

            <a href="{{ route('reports.sales') }}"
                class="flex items-center gap-3 p-3 rounded-lg hover:bg-base-200 {{ request()->routeIs('reports.sales*') ? 'bg-primary text-primary-content' : '' }}"
                onclick="hideMoreMenu()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                <span>Reports</span>
            </a>

            <a href="{{ route('sales.shifts') }}"
                class="flex items-center gap-3 p-3 rounded-lg hover:bg-base-200 {{ request()->routeIs('sales.shifts*') ? 'bg-primary text-primary-content' : '' }}"
                onclick="hideMoreMenu()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Shifts</span>
            </a>

            @can('admin-access')
                <div class="pt-2 mt-2 border-t border-base-300">
                    <a href="{{ route('admin.users') }}"
                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-base-200 {{ request()->routeIs('admin.users*') ? 'bg-primary text-primary-content' : '' }}"
                        onclick="hideMoreMenu()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        <span>Users</span>
                    </a>

                    <a href="{{ route('admin.settings') }}"
                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-base-200 {{ request()->routeIs('admin.settings*') ? 'bg-primary text-primary-content' : '' }}"
                        onclick="hideMoreMenu()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>Settings</span>
                    </a>
                </div>
            @endcan

            <div class="pt-2 mt-2 border-t border-base-300">
                <a href="{{ route('profile.show') }}"
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-base-200" onclick="hideMoreMenu()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Profile</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center w-full gap-3 p-3 text-left rounded-lg hover:bg-base-200"
                        onclick="hideMoreMenu()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Pure JavaScript for Bottom Navigation --}}
<script>
    let inventoryMenuOpen = false;
    let moreMenuOpen = false;

    function toggleInventoryMenu() {
        const inventoryMenu = document.getElementById('inventory-menu');
        const moreMenu = document.getElementById('more-menu');

        if (inventoryMenuOpen) {
            hideInventoryMenu();
        } else {
            // Close more menu if open
            if (moreMenuOpen) {
                hideMoreMenu();
            }

            inventoryMenu.classList.remove('hidden');
            inventoryMenuOpen = true;

            // Add event listener to close when clicking outside
            setTimeout(() => {
                document.addEventListener('click', closeInventoryMenuOutside);
            }, 100);
        }
    }

    function hideInventoryMenu() {
        const inventoryMenu = document.getElementById('inventory-menu');
        inventoryMenu.classList.add('hidden');
        inventoryMenuOpen = false;
        document.removeEventListener('click', closeInventoryMenuOutside);
    }

    function closeInventoryMenuOutside(event) {
        const inventoryMenu = document.getElementById('inventory-menu');
        const inventoryButton = event.target.closest('button');

        if (!inventoryMenu.contains(event.target) &&
            (!inventoryButton || !inventoryButton.onclick || inventoryButton.onclick.toString().indexOf(
                'toggleInventoryMenu') === -1)) {
            hideInventoryMenu();
        }
    }

    function toggleMoreMenu() {
        const moreMenu = document.getElementById('more-menu');
        const inventoryMenu = document.getElementById('inventory-menu');

        if (moreMenuOpen) {
            hideMoreMenu();
        } else {
            // Close inventory menu if open
            if (inventoryMenuOpen) {
                hideInventoryMenu();
            }

            moreMenu.classList.remove('hidden');
            moreMenuOpen = true;

            // Add event listener to close when clicking outside
            setTimeout(() => {
                document.addEventListener('click', closeMoreMenuOutside);
            }, 100);
        }
    }

    function hideMoreMenu() {
        const moreMenu = document.getElementById('more-menu');
        moreMenu.classList.add('hidden');
        moreMenuOpen = false;
        document.removeEventListener('click', closeMoreMenuOutside);
    }

    function closeMoreMenuOutside(event) {
        const moreMenu = document.getElementById('more-menu');
        const moreButton = event.target.closest('button');

        if (!moreMenu.contains(event.target) &&
            (!moreButton || !moreButton.onclick || moreButton.onclick.toString().indexOf('toggleMoreMenu') === -1)) {
            hideMoreMenu();
        }
    }

    // Close menus on navigation
    document.addEventListener('livewire:navigated', function() {
        hideInventoryMenu();
        hideMoreMenu();
    });
</script>

{{-- Add padding to body to account for bottom nav --}}
<style>
    @media (max-width: 1023px) {
        body {
            padding-bottom: 4rem;
            /* 64px for bottom nav */
        }
    }

    .safe-area-bottom {
        padding-bottom: env(safe-area-inset-bottom);
    }
</style>
