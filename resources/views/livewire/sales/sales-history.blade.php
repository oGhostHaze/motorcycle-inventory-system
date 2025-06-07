<div>
    {{-- Page Header --}}
    <x-mary-header title="Sales History" subtitle="View and manage all sales transactions" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search sales..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-document-arrow-down" class="btn-ghost" tooltip="Export Data" />
            <x-mary-button icon="o-arrow-path" wire:click="$refresh" class="btn-ghost" tooltip="Refresh" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <x-mary-stat title="Total Sales" description="Number of transactions" value="{{ number_format($totalSales) }}"
            icon="o-shopping-cart" color="text-primary" />

        <x-mary-stat title="Total Amount" description="Revenue generated" value="₱{{ number_format($totalAmount, 2) }}"
            icon="o-banknotes" color="text-success" />

        <x-mary-stat title="Average Value" description="Per transaction" value="₱{{ number_format($averageValue, 2) }}"
            icon="o-chart-bar" color="text-info" />
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-7">
        <x-mary-select placeholder="All Customers" :options="$filterOptions['customers']" wire:model.live="customerFilter" />
        <x-mary-select placeholder="All Warehouses" :options="$filterOptions['warehouses']" wire:model.live="warehouseFilter" />
        <x-mary-select placeholder="All Users" :options="$filterOptions['users']" wire:model.live="userFilter" />
        <x-mary-select placeholder="All Status" :options="$filterOptions['statuses']" wire:model.live="statusFilter" />
        <x-mary-select placeholder="Payment Method" :options="$filterOptions['paymentMethods']" wire:model.live="paymentMethodFilter" />
        <x-mary-select placeholder="Date Range" :options="$filterOptions['dates']" wire:model.live="dateFilter" />
        <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
            Clear
        </x-mary-button>
    </div>

    {{-- Custom Date Range --}}
    @if ($dateFilter === 'custom')
        <div class="grid grid-cols-2 gap-4 mb-6">
            <x-mary-input label="Start Date" wire:model.live="startDate" type="date" />
            <x-mary-input label="End Date" wire:model.live="endDate" type="date" />
        </div>
    @endif

    {{-- Sales Table --}}
    <x-mary-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>
                                <div>
                                    <div class="font-bold">{{ $sale->invoice_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $sale->warehouse->name }}</div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="font-medium">{{ $sale->customer->name ?? 'Walk-in Customer' }}</div>
                                    @if ($sale->customer)
                                        <div class="text-sm text-gray-500">{{ $sale->customer->email }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div>{{ $sale->created_at->format('M d, Y') }}</div>
                                    <div class="text-gray-500">{{ $sale->created_at->format('H:i') }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span class="font-semibold">{{ $sale->items->count() }}</span>
                                    <div class="text-xs text-gray-500">
                                        {{ $sale->items->sum('quantity') }} units
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-right">
                                    <div class="font-bold">₱{{ number_format($sale->total_amount, 2) }}</div>
                                    @if ($sale->discount_amount > 0)
                                        <div class="text-xs text-success">
                                            -₱{{ number_format($sale->discount_amount, 2) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <x-mary-badge value="{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}"
                                    class="badge-{{ $sale->payment_method === 'cash' ? 'success' : ($sale->payment_method === 'card' ? 'info' : 'warning') }} badge-sm" />
                            </td>
                            <td>
                                <x-mary-badge value="{{ ucfirst($sale->status) }}"
                                    class="badge-{{ $sale->status === 'completed' ? 'success' : ($sale->status === 'draft' ? 'warning' : 'error') }}" />
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                                        <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                                    </div>
                                    <ul tabindex="0"
                                        class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                                        <li><a wire:click="viewSaleDetails({{ $sale->id }})">
                                                <x-heroicon-o-eye class="w-4 h-4" /> View Details</a></li>
                                        <li><a wire:click="printInvoice({{ $sale->id }})">
                                                <x-heroicon-o-printer class="w-4 h-4" /> Print Invoice</a></li>
                                        <li><a wire:click="duplicateSale({{ $sale->id }})">
                                                <x-heroicon-o-document-duplicate class="w-4 h-4" /> Duplicate</a></li>
                                        @if ($sale->status === 'completed')
                                            <li><a wire:click="refundSale({{ $sale->id }})"
                                                    wire:confirm="Are you sure you want to refund this sale?"
                                                    class="text-error">
                                                    <x-heroicon-o-arrow-uturn-left class="w-4 h-4" /> Refund</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="py-8">
                                    <x-heroicon-o-shopping-cart class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-gray-500">No sales found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $sales->links() }}
        </div>
    </x-mary-card>

    {{-- Sale Details Modal --}}
    <x-mary-modal wire:model="showDetailsModal" title="Sale Details"
        subtitle="Invoice: {{ $selectedSale?->invoice_number }}" class="w-11/12 max-w-4xl">

        @if ($selectedSale)
            <div class="space-y-6">
                {{-- Sale Information --}}
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <h4 class="font-semibold">Sale Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Invoice:</strong> {{ $selectedSale->invoice_number }}</div>
                            <div><strong>Date:</strong> {{ $selectedSale->created_at->format('M d, Y H:i') }}</div>
                            <div><strong>Warehouse:</strong> {{ $selectedSale->warehouse->name }}</div>
                            <div><strong>Cashier:</strong> {{ $selectedSale->user->name }}</div>
                            <div><strong>Status:</strong>
                                <x-mary-badge value="{{ ucfirst($selectedSale->status) }}"
                                    class="badge-{{ $selectedSale->status === 'completed' ? 'success' : 'warning' }} badge-sm" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h4 class="font-semibold">Customer Information</h4>
                        <div class="space-y-2 text-sm">
                            @if ($selectedSale->customer)
                                <div><strong>Name:</strong> {{ $selectedSale->customer->name }}</div>
                                <div><strong>Email:</strong> {{ $selectedSale->customer->email }}</div>
                                <div><strong>Phone:</strong> {{ $selectedSale->customer->phone }}</div>
                            @else
                                <div class="text-gray-500">Walk-in Customer</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div>
                    <h4 class="mb-3 font-semibold">Items Sold</h4>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($selectedSale->items as $item)
                                    <tr>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->product_sku }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                        <td>₱{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Totals --}}
                <div class="p-4 rounded-lg bg-base-200">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>₱{{ number_format($selectedSale->subtotal, 2) }}</span>
                        </div>
                        @if ($selectedSale->discount_amount > 0)
                            <div class="flex justify-between text-success">
                                <span>Discount:</span>
                                <span>-₱{{ number_format($selectedSale->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span>Tax:</span>
                            <span>₱{{ number_format($selectedSale->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 text-lg font-bold border-t">
                            <span>Total:</span>
                            <span>₱{{ number_format($selectedSale->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Paid:</span>
                            <span>₱{{ number_format($selectedSale->paid_amount, 2) }}</span>
                        </div>
                        @if ($selectedSale->change_amount > 0)
                            <div class="flex justify-between">
                                <span>Change:</span>
                                <span>₱{{ number_format($selectedSale->change_amount, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Close" wire:click="$set('showDetailsModal', false)" />
            @if ($selectedSale)
                <x-mary-button label="Print Invoice" wire:click="printInvoice({{ $selectedSale->id }})"
                    class="btn-primary" />
            @endif
        </x-slot:actions>
    </x-mary-modal>
</div>
