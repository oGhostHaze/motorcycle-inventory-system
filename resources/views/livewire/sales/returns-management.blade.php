<div>
    {{-- Page Header --}}
    <x-mary-header title="Returns & Exchanges" subtitle="Manage product returns and exchanges" separator>
        <x-slot:actions>
            <x-mary-button label="New Return" wire:click="openReturnModal" icon="o-arrow-uturn-left" class="btn-primary" />
            <x-mary-button icon="o-arrow-path" wire:click="$refresh" class="btn-ghost" tooltip="Refresh" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Summary Stats --}}

    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <x-mary-stat title="Total Returns" value="{{ number_format($totalReturns) }}" icon="o-arrow-uturn-left"
            color="text-primary" />
        <x-mary-stat title="Pending Returns" value="{{ number_format($pendingReturns) }}" icon="o-clock"
            color="text-warning" />
        <x-mary-stat title="Processed Returns" value="{{ number_format($processedReturns) }}" icon="o-check-circle"
            color="text-success" />
        <x-mary-stat title="Actually Refunded" value="₱{{ number_format($totalRefundAmount, 2) }}"
            icon="o-currency-dollar" color="text-success" />
    </div>

    {{-- Filters --}}
    <x-mary-card class="mb-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <x-mary-input label="Search" wire:model.live.debounce.300ms="search" icon="o-magnifying-glass"
                placeholder="Search return number, invoice..." />

            <x-mary-select label="Status" wire:model.live="statusFilter" :options="$filterOptions['statuses']" />

            <x-mary-select label="Type" wire:model.live="typeFilter" :options="$filterOptions['types']" />

            <x-mary-select label="Reason" wire:model.live="reasonFilter" :options="$filterOptions['reasons']" />
        </div>

        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
            <x-mary-select label="Date Range" wire:model.live="dateFilter" :options="$filterOptions['dates']" />

            @if ($dateFilter === 'custom')
                <x-mary-input label="Start Date" wire:model.live="startDate" type="date" />
                <x-mary-input label="End Date" wire:model.live="endDate" type="date" />
            @endif

            <div class="flex items-end">
                <x-mary-button label="Clear Filters" wire:click="clearFilters" class="btn-ghost" />
            </div>
        </div>
    </x-mary-card>

    {{-- Returns Table --}}
    <x-mary-card>
        <div class="min-h-screen overflow-x-auto">
            <table class="table h-full table-zebra">
                <thead>
                    <tr>
                        <th>Return #</th>
                        <th>Date</th>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returns as $return)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $return->return_number }}</div>
                                <div class="text-xs text-gray-500">{{ $return->warehouse->name }}</div>
                            </td>
                            <td>
                                <div>{{ $return->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $return->created_at->format('H:i') }}</div>
                            </td>
                            <td>
                                <div class="font-medium">{{ $return->sale->invoice_number }}</div>
                                <div class="text-xs text-gray-500">{{ $return->user->name }}</div>
                            </td>
                            <td>
                                <div class="font-medium">{{ $return->sale->customer->name ?? 'Walk-in' }}</div>
                                <div class="text-xs text-gray-500">{{ $return->getTotalItemsCount() }} item(s)</div>
                            </td>
                            <td>
                                <x-mary-badge value="{{ $return->type_display }}"
                                    class="badge-{{ $return->type === 'refund' ? 'error' : ($return->type === 'exchange' ? 'info' : 'warning') }} badge-sm" />
                            </td>
                            <td>
                                <div class="text-sm">{{ $return->reason_display }}</div>
                            </td>
                            <td class="font-semibold">₱{{ number_format($return->refund_amount, 2) }}</td>
                            <td>
                                <x-mary-badge value="{{ ucfirst($return->status) }}"
                                    class="badge-{{ $return->status_color }}" />
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                                        <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                                    </div>
                                    <ul tabindex="0"
                                        class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                                        <li><a wire:click="viewReturnDetails({{ $return->id }})">
                                                <x-heroicon-o-eye class="w-4 h-4" /> View Details</a></li>

                                        @if ($return->canBeApproved())
                                            <li><a wire:click="approveReturn({{ $return->id }})"
                                                    wire:confirm="Are you sure you want to approve this return?"
                                                    class="text-success">
                                                    <x-heroicon-o-check-circle class="w-4 h-4" /> Approve</a></li>
                                            <li><a wire:click="rejectReturn({{ $return->id }})"
                                                    wire:confirm="Are you sure you want to reject this return?"
                                                    class="text-error">
                                                    <x-heroicon-o-x-circle class="w-4 h-4" /> Reject</a></li>
                                        @endif

                                        @if ($return->canBeProcessed())
                                            <li><a wire:click="processApprovedReturn({{ $return->id }})"
                                                    wire:confirm="Are you sure you want to process this return? This will restore inventory and process refunds."
                                                    class="text-info">
                                                    <x-heroicon-o-cog-6-tooth class="w-4 h-4" /> Process</a></li>
                                        @endif

                                        @if ($return->canBeCancelled())
                                            <li><a wire:click="cancelProcessedReturn({{ $return->id }})"
                                                    wire:confirm="Are you sure you want to cancel this processed return? This will reverse all inventory and refund changes."
                                                    class="text-warning">
                                                    <x-heroicon-o-x-mark class="w-4 h-4" /> Cancel Return</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="py-8">
                                    <x-heroicon-o-arrow-uturn-left class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-gray-500">No returns found</p>
                                    <x-mary-button label="Create First Return" wire:click="openReturnModal"
                                        class="mt-4 btn-primary btn-sm" />
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $returns->links() }}
        </div>
    </x-mary-card>

    {{-- Create Return Modal --}}
    <x-mary-modal wire:model="showReturnModal" title="Create Return" subtitle="Process a product return"
        box-class="max-w-6xl">
        @if (!$selectedSale)
            {{-- Search for Sale --}}
            <div class="mb-6">
                <x-mary-input label="Invoice Number" wire:model.live.debounce.500ms="search"
                    wire:keydown.enter="searchSaleForReturn" placeholder="Enter invoice number to find sale..."
                    icon="o-magnifying-glass">
                    <x-slot:append>
                        <x-mary-button label="Search" wire:click="searchSaleForReturn" class="btn-primary" />
                    </x-slot:append>
                </x-mary-input>
                <div class="mt-2 text-sm text-gray-600">
                    Enter the invoice number of the completed sale you want to process a return for.
                </div>
            </div>
        @else
            {{-- Sale Found - Show Return Form --}}
            <div class="space-y-6">
                {{-- Sale Information --}}
                <div class="p-4 rounded-lg bg-base-200">
                    <h4 class="mb-3 font-semibold">Sale Information</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><strong>Invoice:</strong> {{ $selectedSale->invoice_number }}</div>
                        <div><strong>Date:</strong> {{ $selectedSale->created_at->format('M d, Y H:i') }}</div>
                        <div><strong>Customer:</strong> {{ $selectedSale->customer->name ?? 'Walk-in Customer' }}</div>
                        <div><strong>Total:</strong> ₱{{ number_format($selectedSale->total_amount, 2) }}</div>
                    </div>
                </div>

                {{-- Return Details --}}
                <div class="grid grid-cols-2 gap-4">
                    <x-mary-select label="Return Type" wire:model.live="returnType" required :options="[
                        ['id' => 'refund', 'name' => 'Refund'],
                        ['id' => 'exchange', 'name' => 'Exchange'],
                        ['id' => 'store_credit', 'name' => 'Store Credit'],
                    ]"
                        placeholder-value="refund" />

                    <x-mary-select label="Restock Condition" wire:model.live="restockCondition" required
                        :options="[
                            ['id' => 'good', 'name' => 'Good Condition'],
                            ['id' => 'damaged', 'name' => 'Damaged'],
                            ['id' => 'defective', 'name' => 'Defective'],
                        ]" placeholder-value="good" />
                </div>

                <x-mary-select label="Return Reason" wire:model.live="returnReason" required :options="[
                    ['id' => 'defective', 'name' => 'Defective Product'],
                    ['id' => 'wrong_item', 'name' => 'Wrong Item'],
                    ['id' => 'not_as_described', 'name' => 'Not as Described'],
                    ['id' => 'customer_changed_mind', 'name' => 'Customer Changed Mind'],
                    ['id' => 'damaged_shipping', 'name' => 'Damaged in Shipping'],
                    ['id' => 'warranty_claim', 'name' => 'Warranty Claim'],
                    ['id' => 'other', 'name' => 'Other'],
                ]"
                    placeholder-value="defective" />

                <x-mary-textarea label="Additional Notes" wire:model="returnNotes"
                    placeholder="Enter any additional notes about this return..." rows="3" />

                {{-- Items to Return --}}
                <div>
                    <h4 class="mb-3 font-semibold">Select Items to Return</h4>
                    @if (empty($returnItems))
                        <div class="p-4 text-center rounded-lg bg-warning/10">
                            <x-heroicon-o-exclamation-triangle class="w-8 h-8 mx-auto mb-2 text-warning" />
                            <p class="font-medium text-warning">All items from this sale have already been returned.
                            </p>
                            <p class="text-sm text-gray-600">No items are available for return.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Select</th>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Sold Qty</th>
                                        <th>Returned</th>
                                        <th>Available</th>
                                        <th>Return Qty</th>
                                        <th>Unit Price</th>
                                        <th>Return Total</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($returnItems as $index => $item)
                                        <tr class="{{ $item['selected'] ? 'bg-primary/10' : '' }}"
                                            wire:key="item-{{ $index }}">
                                            <td>
                                                <x-mary-checkbox
                                                    wire:model.live="returnItems.{{ $index }}.selected" />
                                            </td>
                                            <td>
                                                <div class="font-medium">{{ $item['product_name'] }}</div>
                                            </td>
                                            <td>{{ $item['sku'] }}</td>
                                            <td>{{ $item['original_quantity'] }}</td>
                                            <td>
                                                <span
                                                    class="badge badge-error badge-sm">{{ $item['already_returned'] }}</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-success badge-sm">{{ $item['available_to_return'] }}</span>
                                            </td>
                                            <td>
                                                @if ($item['selected'])
                                                    <x-mary-input type="number"
                                                        wire:model.live="returnItems.{{ $index }}.quantity"
                                                        wire:change="updateReturnQuantity({{ $index }}, $event.target.value)"
                                                        min="1" max="{{ $item['available_to_return'] }}"
                                                        class="w-20 input-sm" />
                                                @else
                                                    0
                                                @endif
                                            </td>
                                            <td>₱{{ number_format($item['unit_price'], 2) }}</td>
                                            <td class="font-semibold">
                                                ₱{{ number_format(($item['selected'] ? (float) $item['quantity'] : 0) * (float) $item['unit_price'], 2) }}
                                            </td>
                                            <td>
                                                @if ($item['selected'])
                                                    <x-mary-select wire:model="returnItems.{{ $index }}.reason"
                                                        :options="[
                                                            ['id' => 'wrong_item', 'name' => 'Wrong Item'],
                                                            ['id' => 'defective', 'name' => 'Defective'],
                                                            ['id' => 'not_as_described', 'name' => 'Not as Described'],
                                                            ['id' => 'customer_changed_mind', 'name' => 'Changed Mind'],
                                                            ['id' => 'damaged_shipping', 'name' => 'Damaged'],
                                                            ['id' => 'warranty_claim', 'name' => 'Warranty'],
                                                            ['id' => 'other', 'name' => 'Other'],
                                                        ]" placeholder="Select Reason"
                                                        placeholder-value="n/a" />
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Return Summary --}}
                <div class="p-4 rounded-lg bg-success/10">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold">Total Return Amount:</span>
                        <span class="text-xl font-bold text-success">₱{{ number_format($refundAmount, 2) }}</span>
                    </div>
                </div>
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showReturnModal', false)" />
            @if ($selectedSale)
                <x-mary-button label="Process Return" wire:click="processReturn" class="btn-primary" />
            @endif
        </x-slot:actions>
    </x-mary-modal>

    {{-- Return Details Modal --}}
    <x-mary-modal wire:model="showDetailsModal" title="Return Details"
        subtitle="Return #{{ $selectedReturn?->return_number }}" box-class="max-w-4xl">

        @if ($selectedReturn)
            <div class="space-y-6">
                {{-- Return Information --}}
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <h4 class="font-semibold">Return Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Return #:</strong> {{ $selectedReturn->return_number }}</div>
                            <div><strong>Date:</strong> {{ $selectedReturn->created_at->format('M d, Y H:i') }}</div>
                            <div><strong>Type:</strong> {{ $selectedReturn->type_display }}</div>
                            <div><strong>Reason:</strong> {{ $selectedReturn->reason_display }}</div>
                            <div><strong>Status:</strong>
                                <x-mary-badge value="{{ ucfirst($selectedReturn->status) }}"
                                    class="badge-{{ $selectedReturn->status_color }}" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h4 class="font-semibold">Sale Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Invoice:</strong> {{ $selectedReturn->sale->invoice_number }}</div>
                            <div><strong>Customer:</strong> {{ $selectedReturn->sale->customer->name ?? 'Walk-in' }}
                            </div>
                            <div><strong>Warehouse:</strong> {{ $selectedReturn->warehouse->name }}</div>
                            <div><strong>Processed By:</strong> {{ $selectedReturn->user->name }}</div>
                        </div>
                    </div>
                </div>

                {{-- Return Items --}}
                <div>
                    <h4 class="mb-3 font-semibold">Returned Items</h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Condition</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($selectedReturn->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->product->sku }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                        <td>₱{{ number_format($item->total_price, 2) }}</td>
                                        <td>
                                            <x-mary-badge value="{{ $item->condition_display }}"
                                                class="badge-{{ $item->condition_color }} badge-sm" />
                                        </td>
                                        <td>{{ $item->reason_display }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Return Summary --}}
                <div class="p-4 rounded-lg bg-base-200">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold">Total Return Amount:</span>
                        <span class="text-xl font-bold">₱{{ number_format($selectedReturn->refund_amount, 2) }}</span>
                    </div>
                </div>

                {{-- Notes --}}
                @if ($selectedReturn->notes)
                    <div>
                        <h4 class="mb-2 font-semibold">Notes</h4>
                        <div class="p-3 text-sm rounded-lg bg-base-200">
                            {{ $selectedReturn->notes }}
                        </div>
                    </div>
                @endif

                {{-- Approval/Processing Information --}}
                @if ($selectedReturn->status !== 'pending')
                    <div class="p-4 rounded-lg bg-info/10">
                        <h4 class="mb-2 font-semibold">Processing History</h4>
                        <div class="space-y-1 text-sm">
                            @if ($selectedReturn->approved_at)
                                <div><strong>Approved:</strong>
                                    {{ $selectedReturn->approved_at->format('M d, Y H:i') }}
                                    by {{ $selectedReturn->approvedBy->name }}</div>
                            @endif
                            @if ($selectedReturn->processed_at)
                                <div><strong>Processed:</strong>
                                    {{ $selectedReturn->processed_at->format('M d, Y H:i') }}
                                    by {{ $selectedReturn->processedBy->name }}</div>
                            @endif
                            @if ($selectedReturn->rejected_at)
                                <div><strong>Rejected:</strong>
                                    {{ $selectedReturn->rejected_at->format('M d, Y H:i') }}
                                    by {{ $selectedReturn->rejectedBy->name }}</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Close" wire:click="$set('showDetailsModal', false)" />
        </x-slot:actions>
    </x-mary-modal>
</div>
