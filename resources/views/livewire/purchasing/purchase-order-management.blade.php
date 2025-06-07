<div>
    {{-- Page Header --}}
    <x-mary-header title="Purchase Orders" subtitle="Manage supplier purchase orders and receiving" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search purchase orders..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" wire:click="openModal" class="btn-primary">
                Create Purchase Order
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-5">
        <x-mary-select placeholder="All Suppliers" :options="$filterOptions['suppliers']" wire:model.live="supplierFilter"
            option-value="value" option-label="label" />
        <x-mary-select placeholder="All Warehouses" :options="$filterOptions['warehouses']" wire:model.live="warehouseFilter"
            option-value="value" option-label="label" />
        <x-mary-select placeholder="All Status" :options="$filterOptions['statuses']" wire:model.live="statusFilter" option-value="value"
            option-label="label" />
        <x-mary-select placeholder="All Dates" :options="$filterOptions['dates']" wire:model.live="dateFilter" option-value="value"
            option-label="label" />
        <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
            Clear Filters
        </x-mary-button>
    </div>

    {{-- Purchase Orders Table --}}
    <x-mary-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Warehouse</th>
                        <th>Order Date</th>
                        <th>Expected</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                        <tr>
                            <td>
                                <div>
                                    <div class="font-bold">{{ $po->po_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $po->requestedBy->name }}</div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="font-medium">{{ $po->supplier->name }}</div>
                                    @if ($po->supplier->lead_time_days)
                                        <div class="text-sm text-gray-500">{{ $po->supplier->lead_time_days }} days lead
                                            time</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $po->warehouse->name }}</span>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div>{{ $po->order_date->format('M d, Y') }}</div>
                                    <div class="text-gray-500">{{ $po->order_date->diffForHumans() }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div
                                        class="{{ $po->expected_date->isPast() && $po->status !== 'completed' ? 'text-error font-medium' : '' }}">
                                        {{ $po->expected_date->format('M d, Y') }}
                                    </div>
                                    @if ($po->expected_date->isPast() && $po->status !== 'completed')
                                        <div class="text-xs text-error">Overdue</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span class="font-semibold">{{ $po->items->count() }}</span>
                                    <div class="text-xs text-gray-500">
                                        {{ $po->items->sum('quantity_ordered') }} units
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-right">
                                    <div class="font-bold">₱{{ number_format($po->total_amount, 2) }}</div>
                                </div>
                            </td>
                            <td>
                                <x-mary-badge value="{{ ucfirst($po->status) }}"
                                    class="badge-{{ $po->status === 'completed' ? 'success' : ($po->status === 'pending' ? 'warning' : ($po->status === 'partial' ? 'info' : ($po->status === 'cancelled' ? 'error' : 'ghost'))) }}" />
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                                        <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                                    </div>
                                    <ul tabindex="0"
                                        class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                                        @if ($po->status === 'draft')
                                            <li><a wire:click="editPO({{ $po->id }})">
                                                    <x-heroicon-o-pencil class="w-4 h-4" /> Edit</a></li>
                                            <li><a wire:click="submitPO({{ $po->id }})">
                                                    <x-heroicon-o-paper-airplane class="w-4 h-4" /> Submit</a></li>
                                        @endif
                                        @if (in_array($po->status, ['pending', 'partial']))
                                            <li><a wire:click="openReceiveModal({{ $po->id }})">
                                                    <x-heroicon-o-truck class="w-4 h-4" /> Receive Items</a></li>
                                        @endif
                                        <li><a href="#" class="text-info">
                                                <x-heroicon-o-eye class="w-4 h-4" /> View Details</a></li>
                                        <li><a href="#" class="text-info">
                                                <x-heroicon-o-printer class="w-4 h-4" /> Print PO</a></li>
                                        @if (in_array($po->status, ['draft', 'pending']))
                                            <li><a wire:click="cancelPO({{ $po->id }})"
                                                    wire:confirm="Are you sure?" class="text-error">
                                                    <x-heroicon-o-x-mark class="w-4 h-4" /> Cancel</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="py-8">
                                    <x-heroicon-o-document-text class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-gray-500">No purchase orders found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $purchaseOrders->links() }}
        </div>
    </x-mary-card>

    {{-- Create/Edit PO Modal --}}
    <x-mary-modal wire:model="showModal" title="{{ $editMode ? 'Edit Purchase Order' : 'Create Purchase Order' }}"
        subtitle="Manage purchase order details and items">

        <div class="space-y-6">
            {{-- Basic Information --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <x-mary-select label="Supplier" :options="$suppliers->map(fn($s) => ['value' => $s->id, 'label' => $s->name])" wire:model="supplier_id" placeholder="Select supplier"
                    option-value="value" option-label="label" />

                <x-mary-select label="Warehouse" :options="$warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name])" wire:model="warehouse_id"
                    placeholder="Select warehouse" option-value="value" option-label="label" />

                <div class="flex items-center">
                    <!-- Placeholder for future features -->
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="Order Date" wire:model="order_date" type="date" />
                <x-mary-input label="Expected Delivery" wire:model="expected_date" type="date" />
            </div>

            {{-- Items Section --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold">Order Items</h4>
                    <x-mary-button icon="o-plus" wire:click="addItem" class="btn-sm btn-primary">
                        Add Item
                    </x-mary-button>
                </div>

                @if (count($items) > 0)
                    <div class="space-y-3">
                        @foreach ($items as $index => $item)
                            <div class="p-4 border rounded-lg bg-base-200">
                                <div class="grid items-end grid-cols-5 gap-3">
                                    <x-mary-select label="Product" :options="$products->map(
                                        fn($p) => ['value' => $p->id, 'label' => $p->name . ' (' . $p->sku . ')'],
                                    )" option-value="value"
                                        option-label="label" wire:model="items.{{ $index }}.product_id"
                                        placeholder="Select product" />

                                    <x-mary-input label="Quantity" wire:model="items.{{ $index }}.quantity"
                                        type="number" min="1" />

                                    <x-mary-input label="Unit Cost" wire:model="items.{{ $index }}.unit_cost"
                                        type="number" step="0.01" />

                                    <div class="text-center">
                                        <label class="block mb-1 text-sm font-medium">Total</label>
                                        <div class="font-bold">
                                            ₱{{ number_format(($item['quantity'] ?? 0) * ($item['unit_cost'] ?? 0), 2) }}
                                        </div>
                                    </div>

                                    <x-mary-button icon="o-trash" wire:click="removeItem({{ $index }})"
                                        class="btn-ghost btn-sm text-error" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Total --}}
                    <div class="p-4 mt-4 rounded-lg bg-primary/10">
                        <div class="text-right">
                            <span class="text-lg font-bold">
                                Total:
                                ₱{{ number_format(collect($items)->sum(fn($item) => ($item['quantity'] ?? 0) * ($item['unit_cost'] ?? 0)), 2) }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center border-2 border-gray-300 border-dashed rounded-lg">
                        <x-heroicon-o-plus class="w-12 h-12 mx-auto text-gray-400" />
                        <p class="mt-2 text-gray-500">No items added</p>
                        <x-mary-button label="Add First Item" wire:click="addItem" class="mt-3 btn-primary" />
                    </div>
                @endif
            </div>

            {{-- Notes --}}
            <x-mary-textarea label="Notes" wire:model="notes" placeholder="Additional notes or instructions"
                rows="3" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showModal', false)" />
            <x-mary-button label="{{ $editMode ? 'Update PO' : 'Save as Draft' }}" wire:click="save"
                class="btn-primary" :disabled="count($items) === 0" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Receiving Modal --}}
    <x-mary-modal wire:model="showReceiveModal" title="Receive Items" subtitle="PO: {{ $selectedPO?->po_number }}">

        @if ($selectedPO)
            <div class="space-y-4">
                <div class="p-4 rounded-lg bg-info/10">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><strong>Supplier:</strong> {{ $selectedPO->supplier->name }}</div>
                        <div><strong>Expected:</strong> {{ $selectedPO->expected_date->format('M d, Y') }}</div>
                        <div><strong>Warehouse:</strong> {{ $selectedPO->warehouse->name }}</div>
                        <div><strong>Status:</strong> {{ ucfirst($selectedPO->status) }}</div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Ordered</th>
                                <th>Received</th>
                                <th>Pending</th>
                                <th>Receiving Now</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($receivingItems as $index => $item)
                                <tr>
                                    <td>
                                        <div class="font-medium">{{ $item['product_name'] }}</div>
                                    </td>
                                    <td class="text-center">{{ $item['quantity_ordered'] }}</td>
                                    <td class="text-center">{{ $item['quantity_received'] }}</td>
                                    <td class="font-bold text-center text-warning">{{ $item['quantity_pending'] }}
                                    </td>
                                    <td>
                                        <x-mary-input
                                            wire:model="receivingItems.{{ $index }}.receiving_quantity"
                                            type="number" min="0" max="{{ $item['quantity_pending'] }}"
                                            class="input-sm" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" wire:click="$set('showReceiveModal', false)" />
                <x-mary-button label="Process Receiving" wire:click="processReceiving" class="btn-success" />
            </x-slot:actions>
        @endif
    </x-mary-modal>
</div>
