<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 lg:p-8">
                    <h1 class="text-2xl font-medium text-gray-900">
                        {{ $title ?? 'Feature Coming Soon' }}
                    </h1>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 bg-gray-200 bg-opacity-25 lg:gap-8 lg:p-8">
                    <div class="py-16 text-center">
                        <div class="flex items-center justify-center w-32 h-32 mx-auto mb-8 rounded-full bg-primary/10">
                            <svg class="w-16 h-16 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
                                </path>
                            </svg>
                        </div>

                        <h2 class="mb-4 text-3xl font-bold text-gray-900">
                            {{ $message ?? 'This feature is under development' }}
                        </h2>

                        <p class="max-w-2xl mx-auto mb-8 text-lg text-gray-600">
                            We're working hard to bring you this feature. It will be available in a future update of the
                            motorcycle parts inventory system.
                        </p>

                        <div class="space-y-4">
                            <div class="flex justify-center space-x-4">
                                <a href="{{ route('dashboard') }}"
                                    class="inline-flex items-center px-6 py-3 text-sm font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out rounded-md bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                        </path>
                                    </svg>
                                    Back to Dashboard
                                </a>

                                <button onclick="history.back()"
                                    class="inline-flex items-center px-6 py-3 text-sm font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Go Back
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Feature Preview Cards --}}
                    <div class="mt-16">
                        <h3 class="mb-8 text-xl font-semibold text-center text-gray-900">Upcoming Features</h3>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                                <div class="flex items-center mb-4">
                                    <div class="flex items-center justify-center w-8 h-8 mr-3 bg-blue-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900">Advanced Analytics</h4>
                                </div>
                                <p class="text-sm text-gray-600">Comprehensive reports and data visualization for better
                                    business insights.</p>
                            </div>

                            <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                                <div class="flex items-center mb-4">
                                    <div class="flex items-center justify-center w-8 h-8 mr-3 bg-green-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0A17.5 17.5 0 0015 3c-2.36 0-4.54.58-6.5 1.5">
                                            </path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900">Barcode Integration</h4>
                                </div>
                                <p class="text-sm text-gray-600">Advanced barcode scanning and RFID integration for
                                    faster inventory management.</p>
                            </div>

                            <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                                <div class="flex items-center mb-4">
                                    <div class="flex items-center justify-center w-8 h-8 mr-3 bg-purple-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                            </path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900">Smart Automation</h4>
                                </div>
                                <p class="text-sm text-gray-600">Intelligent reordering, warranty tracking, and
                                    automated business processes.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
