<div>
    {{-- Page Header --}}
    <x-mary-header title="Sales History" subtitle="View and manage sales transactions" separator>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-path" wire:click="$refresh" class="btn-ghost" tooltip="Refresh" />
            <x-mary-button icon="o-funnel" class="btn-ghost" tooltip="Show Filters" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <x-mary-stat title="Total Sales" value="{{ number_format($totalSales) }}" icon="o-shopping-cart"
            color="text-primary" />
        <x-mary-stat title="Total Amount" value="₱{{ number_format($totalAmount, 2) }}" icon="o-currency-dollar"
            color="text-success" />
        <x-mary-stat title="Average Value" value="₱{{ number_format($averageValue, 2) }}" icon="o-chart-bar"
            color="text-info" />
    </div>

    {{-- Filters --}}
    <x-mary-card class="mb-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <x-mary-input label="Search" wire:model.live.debounce.300ms="search" icon="o-magnifying-glass"
                placeholder="Search invoice, customer..." />

            <x-mary-select label="Customer" wire:model.live="customerFilter" :options="[
                ['id' => '', 'name' => 'All Customers'],
                ...$customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toArray(),
            ]" />

            <x-mary-select label="Status" wire:model.live="statusFilter" :options="$filterOptions['statuses']" />

            <x-mary-select label="Payment Method" wire:model.live="paymentMethodFilter" :options="$filterOptions['paymentMethods']" />
        </div>

        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
            <x-mary-select label="Warehouse" wire:model.live="warehouseFilter" :options="[
                ['id' => '', 'name' => 'All Warehouses'],
                ...$warehouses->map(fn($w) => ['id' => $w->id, 'name' => $w->name])->toArray(),
            ]" />

            <x-mary-select label="Date Range" wire:model.live="dateFilter" :options="$filterOptions['dates']" />

            <div class="flex items-end gap-2">
                @if ($dateFilter === 'custom')
                    <x-mary-input label="Start Date" wire:model.live="startDate" type="date" />
                    <x-mary-input label="End Date" wire:model.live="endDate" type="date" />
                @endif
                <x-mary-button label="Clear" wire:click="clearFilters" class="btn-ghost" />
            </div>
        </div>
    </x-mary-card>

    {{-- Sales Table --}}
    <x-mary-card>
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $sale->invoice_number }}</div>
                                <div class="text-xs text-gray-500">{{ $sale->warehouse->name }}</div>
                            </td>
                            <td>
                                <div>{{ $sale->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $sale->created_at->format('H:i') }}</div>
                            </td>
                            <td>
                                <div class="font-medium">{{ $sale->customer->name ?? 'Walk-in Customer' }}</div>
                                <div class="text-xs text-gray-500">{{ $sale->user->name }}</div>
                            </td>
                            <td>
                                <x-mary-badge value="{{ $sale->items->count() }} item(s)" class="badge-info badge-sm" />
                            </td>
                            <td class="font-semibold">₱{{ number_format($sale->total_amount, 2) }}</td>
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
                                        <li><a href="{{ route('invoice.preview', $sale->id) }}" target="_blank"
                                                class="flex items-center gap-2">
                                                <x-heroicon-o-printer class="w-4 h-4" /> Print Invoice</a></li>
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
        subtitle="Invoice: {{ $selectedSale?->invoice_number }}">

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
                                    class="badge-{{ $selectedSale->status === 'completed' ? 'success' : ($selectedSale->status === 'draft' ? 'warning' : 'error') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h4 class="font-semibold">Customer Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Name:</strong> {{ $selectedSale->customer->name ?? 'Walk-in Customer' }}</div>
                            @if ($selectedSale->customer)
                                <div><strong>Email:</strong> {{ $selectedSale->customer->email ?? 'N/A' }}</div>
                                <div><strong>Phone:</strong> {{ $selectedSale->customer->phone ?? 'N/A' }}</div>
                            @endif
                            <div><strong>Payment:</strong>
                                {{ ucfirst(str_replace('_', ' ', $selectedSale->payment_method)) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Items Table --}}
                <div>
                    <h4 class="mb-3 font-semibold">Items</h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($selectedSale->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->product->sku }}</td>
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
                            <span>₱{{ number_format($selectedSale->subtotal_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Discount:</span>
                            <span>₱{{ number_format($selectedSale->discount_amount, 2) }}</span>
                        </div>
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
                <x-mary-button label="Print Invoice"
                    onclick="window.open('{{ route('invoice.preview', $selectedSale->id) }}', '_blank')"
                    class="btn-primary" />
            @endif
        </x-slot:actions>
    </x-mary-modal>

    {{-- Fixed Download Script with Correct Livewire 3 Event Handling --}}
    @script
        <script>
            // Listen for download trigger with correct parameter access
            Livewire.on('trigger-download', (event) => {
                try {
                    // Access the parameters correctly (Livewire 3 passes them directly)
                    const url = event.url;
                    const filename = event.filename;

                    // Create a temporary anchor element
                    const link = document.createElement('a');
                    link.href = url;
                    link.target = '_blank'; // Open in new tab as fallback

                    // Try to trigger download
                    if (filename) {
                        link.download = filename;
                    }

                    // Add to DOM, click, and remove
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                } catch (error) {
                    console.error('Download error:', error);

                    // Fallback: open in new window
                    if (event.url) {
                        window.open(event.url, '_blank');
                    }
                }
            });
        </script>
    @endscript
</div>
