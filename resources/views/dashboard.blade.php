<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 lg:p-8">
                    <x-application-logo class="block w-auto h-12" />

                    <h1 class="mt-8 text-2xl font-medium text-gray-900">
                        Welcome to Motorcycle Inventory System!
                    </h1>

                    <p class="mt-6 leading-relaxed text-gray-500">
                        Your comprehensive solution for managing motorcycle parts inventory, sales, and business
                        operations.
                        The system is ready with complete database structure and user authentication.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 bg-gray-200 bg-opacity-25 md:grid-cols-2 lg:gap-8 lg:p-8">
                    <div>
                        <div class="flex items-center">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-500">
                                <path
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                            <h2 class="ml-3 text-xl font-semibold text-gray-900">
                                Inventory Management
                            </h2>
                        </div>

                        <p class="mt-4 text-sm leading-relaxed text-gray-500">
                            Complete product catalog with categories for Mags & Rims, Engine Oil, Tires, CVT Parts,
                            Engine Performance parts, Cosmetics, and Exhaust Pipes. Multi-warehouse support with
                            real-time stock tracking.
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-500">
                                <path
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                </path>
                            </svg>
                            <h2 class="ml-3 text-xl font-semibold text-gray-900">
                                Sales & POS
                            </h2>
                        </div>

                        <p class="mt-4 text-sm leading-relaxed text-gray-500">
                            Point of Sale system with customer management, discount handling, and receipt generation.
                            Designed for BIR compliance with draft receipt functionality.
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-500">
                                <path
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z">
                                </path>
                            </svg>
                            <h2 class="ml-3 text-xl font-semibold text-gray-900">
                                Motorcycle Database
                            </h2>
                        </div>

                        <p class="mt-4 text-sm leading-relaxed text-gray-500">
                            Comprehensive motorcycle brands and models database with product compatibility matrix.
                            Supports Yamaha, Honda models like NMAX, Aerox, Click, Beat, M3, and Sporty.
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-500">
                                <path
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                            <h2 class="ml-3 text-xl font-semibold text-gray-900">
                                Reports & Analytics
                            </h2>
                        </div>

                        <p class="mt-4 text-sm leading-relaxed text-gray-500">
                            Comprehensive reporting system with sales analytics, inventory reports, and financial
                            tracking.
                            Activity logs and audit trails for complete business oversight.
                        </p>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200">
                    <div class="text-center">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">System Status</h3>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <div class="p-4 bg-green-100 rounded-lg">
                                <div class="font-semibold text-green-800">Database</div>
                                <div class="text-sm text-green-600">Ready</div>
                            </div>
                            <div class="p-4 bg-green-100 rounded-lg">
                                <div class="font-semibold text-green-800">Authentication</div>
                                <div class="text-sm text-green-600">Active</div>
                            </div>
                            <div class="p-4 bg-yellow-100 rounded-lg">
                                <div class="font-semibold text-yellow-800">Livewire Pages</div>
                                <div class="text-sm text-yellow-600">Pending</div>
                            </div>
                            <div class="p-4 bg-yellow-100 rounded-lg">
                                <div class="font-semibold text-yellow-800">Components</div>
                                <div class="text-sm text-yellow-600">Pending</div>
                            </div>
                        </div>

                        <p class="mt-4 text-sm text-gray-600">
                            Ready for Livewire component development. Next step: Create individual page components.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
