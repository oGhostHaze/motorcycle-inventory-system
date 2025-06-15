<div>
    {{-- Page Header --}}
    <x-mary-header title="Supplier Management" subtitle="Manage supplier information and relationships" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search suppliers..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" wire:click="openModal" class="btn-primary">
                Add Supplier
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-5">
        <x-mary-select placeholder="All Countries" :options="$countries->map(fn($c) => ['value' => $c, 'label' => $c])" wire:model.live="countryFilter"
            option-value="value" option-label="label" />

        <x-mary-select placeholder="All Status" :options="[
            ['value' => '', 'label' => 'All Status'],
            ['value' => '1', 'label' => 'Active'],
            ['value' => '0', 'label' => 'Inactive'],
        ]" wire:model.live="statusFilter" option-value="value"
            option-label="label" />

        <x-mary-select placeholder="All Ratings" :options="[
            ['value' => '', 'label' => 'All Ratings'],
            ['value' => '4', 'label' => '4+ Stars'],
            ['value' => '3', 'label' => '3+ Stars'],
            ['value' => '2', 'label' => '2+ Stars'],
            ['value' => '1', 'label' => '1+ Stars'],
        ]" wire:model.live="ratingFilter" option-value="value"
            option-label="label" />

        <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
            Clear Filters
        </x-mary-button>
    </div>

    {{-- Suppliers Grid --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($suppliers as $supplier)
            <x-mary-card class="h-full transition-all duration-200 hover:shadow-lg">
                {{-- Supplier Header --}}
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold">{{ $supplier->name }}</h3>
                        @if ($supplier->contact_person)
                            <p class="text-sm text-gray-600">{{ $supplier->contact_person }}</p>
                        @endif
                    </div>
                    <div class="flex gap-1">
                        <x-mary-button icon="o-pencil" wire:click="editSupplier({{ $supplier->id }})"
                            class="btn-ghost btn-sm" tooltip="Edit" />
                        <x-mary-button icon="o-cube" wire:click="openProductsModal({{ $supplier->id }})"
                            class="btn-ghost btn-sm" tooltip="Manage Products" />
                        <x-mary-dropdown>
                            <x-slot:trigger>
                                <x-mary-button icon="o-ellipsis-vertical" class="btn-ghost btn-sm" />
                            </x-slot:trigger>
                            <x-mary-menu-item title="Toggle Status" wire:click="toggleStatus({{ $supplier->id }})" />
                            <x-mary-menu-item title="Delete" wire:click="deleteSupplier({{ $supplier->id }})"
                                wire:confirm="Are you sure you want to delete this supplier?" />
                        </x-mary-dropdown>
                    </div>
                </div>

                {{-- Status Badge --}}
                <div class="mb-3">
                    <x-mary-badge value="{{ $supplier->is_active ? 'Active' : 'Inactive' }}"
                        class="{{ $supplier->is_active ? 'badge-success' : 'badge-error' }}" />
                </div>

                {{-- Contact Information --}}
                <div class="mb-4 space-y-1">
                    @if ($supplier->email)
                        <div class="flex items-center text-sm text-gray-600">
                            <x-heroicon-o-envelope class="w-4 h-4 mr-2" />
                            <span>{{ $supplier->email }}</span>
                        </div>
                    @endif

                    @if ($supplier->phone)
                        <div class="flex items-center text-sm text-gray-600">
                            <x-heroicon-o-phone class="w-4 h-4 mr-2" />
                            <span>{{ $supplier->phone }}</span>
                        </div>
                    @endif

                    @if ($supplier->city || $supplier->country)
                        <div class="flex items-center text-sm text-gray-600">
                            <x-heroicon-o-map-pin class="w-4 h-4 mr-2" />
                            <span>{{ $supplier->city ? $supplier->city . ', ' : '' }}{{ $supplier->country }}</span>
                        </div>
                    @endif
                </div>

                {{-- Rating and Lead Time --}}
                <div class="mb-4 space-y-2">
                    @if ($supplier->rating)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Rating:</span>
                            <div class="flex items-center space-x-1">
                                <span class="text-yellow-500">{{ $this->getRatingStars($supplier->rating) }}</span>
                                <span class="text-sm">({{ $supplier->rating }})</span>
                            </div>
                        </div>
                    @endif

                    @if ($supplier->lead_time_days)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Lead Time:</span>
                            <span class="text-sm font-medium">{{ $supplier->lead_time_days }} days</span>
                        </div>
                    @endif
                </div>

                {{-- Statistics --}}
                <div class="grid grid-cols-1 gap-2 mb-4">
                    <div class="p-2 text-center rounded bg-info/10">
                        <div class="text-lg font-bold text-info">{{ $supplier->purchase_orders_count }}</div>
                        <div class="text-xs text-gray-600">Purchase Orders</div>
                    </div>
                </div>

                {{-- Notes --}}
                @if ($supplier->notes)
                    <div class="p-3 text-sm rounded-lg bg-base-200 mb-4">
                        <div class="text-gray-600">{{ Str::limit($supplier->notes, 100) }}</div>
                    </div>
                @endif

                {{-- Quick Actions - FIXED WITH WIRE:CLICK --}}
                <div class="flex gap-2 mt-4">
                    <x-mary-button label="Create PO" wire:click="createPurchaseOrder({{ $supplier->id }})"
                        class="flex-1 btn-outline btn-sm" icon="o-plus" />
                    <x-mary-button label="View Orders" wire:click="viewOrders({{ $supplier->id }})"
                        class="flex-1 btn-outline btn-sm" icon="o-eye" />
                </div>
            </x-mary-card>
        @empty
            <div class="col-span-full">
                <x-mary-card>
                    <div class="py-8 text-center">
                        <x-heroicon-o-building-office-2 class="w-12 h-12 mx-auto text-gray-400" />
                        <p class="mt-2 text-gray-500">No suppliers found</p>
                        <x-mary-button label="Create First Supplier" wire:click="openModal" class="mt-4 btn-primary" />
                    </div>
                </x-mary-card>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $suppliers->links() }}
    </div>

    {{-- Create/Edit Supplier Modal --}}
    <x-mary-modal wire:model="showModal" title="{{ $editMode ? 'Edit Supplier' : 'Create Supplier' }}"
        box-class="max-w-2xl">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-mary-input label="Supplier Name *" wire:model="name" placeholder="Enter supplier name" />
            <x-mary-input label="Contact Person" wire:model="contact_person" placeholder="Enter contact person" />
            <x-mary-input label="Email" wire:model="email" type="email" placeholder="Enter email address" />
            <x-mary-input label="Phone" wire:model="phone" placeholder="Enter phone number" />
            <x-mary-input label="City" wire:model="city" placeholder="Enter city" />
            <x-mary-input label="Country" wire:model="country" placeholder="Enter country" />
            <x-mary-input label="Lead Time (Days)" wire:model="lead_time_days" type="number" min="1" />
            <x-mary-input label="Rating (1-5)" wire:model="rating" type="number" min="1" max="5"
                step="0.1" />
        </div>

        <x-mary-textarea label="Address" wire:model="address" placeholder="Enter full address" />
        <x-mary-textarea label="Notes" wire:model="notes" placeholder="Additional notes about supplier" />

        <x-mary-checkbox label="Active" wire:model="is_active" />

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showModal', false)" />
            <x-mary-button label="{{ $editMode ? 'Update' : 'Create' }}" wire:click="save" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- NEW: View Orders Modal --}}
    <x-mary-modal wire:model="showOrdersModal" title="Purchase Orders - {{ $selectedSupplier?->name }}"
        box-class="max-w-6xl">
        @if ($selectedSupplier)
            <div class="space-y-4">
                {{-- Search --}}
                <x-mary-input label="Search Orders" wire:model.live.debounce.300ms="ordersSearch"
                    icon="o-magnifying-glass" placeholder="Search by PO number" />

                {{-- Orders Summary --}}
                <div class="grid grid-cols-4 gap-4 p-4 rounded-lg bg-base-200">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-info">{{ count($supplierOrders) }}</div>
                        <div class="text-sm text-gray-600">Total Orders</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-warning">
                            {{ $supplierOrders->where('status', 'pending')->count() }}
                        </div>
                        <div class="text-sm text-gray-600">Pending</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-success">
                            {{ $supplierOrders->where('status', 'completed')->count() }}
                        </div>
                        <div class="text-sm text-gray-600">Completed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary">
                            ₱{{ number_format($supplierOrders->sum('total_amount'), 2) }}
                        </div>
                        <div class="text-sm text-gray-600">Total Value</div>
                    </div>
                </div>

                {{-- Orders Table --}}
                @if (count($supplierOrders) > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>PO Number</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($supplierOrders as $order)
                                    <tr>
                                        <td>
                                            <div class="font-medium">{{ $order->po_number }}</div>
                                            <div class="text-sm text-gray-500">by {{ $order->requestedBy->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $order->order_date->format('M d, Y') }}</div>
                                            @if ($order->expected_date)
                                                <div class="text-sm text-gray-500">
                                                    Expected: {{ $order->expected_date->format('M d, Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <x-mary-badge value="{{ ucfirst($order->status) }}"
                                                class="badge badge-{{ $order->status_color }}" />
                                        </td>
                                        <td>{{ $order->items->count() }} items</td>
                                        <td>₱{{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            <x-mary-button label="View"
                                                wire:click="goToPurchaseOrder({{ $order->id }})"
                                                class="btn-sm btn-outline" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center border-2 border-gray-300 border-dashed rounded-lg">
                        <x-heroicon-o-clipboard-document-list class="w-12 h-12 mx-auto text-gray-400" />
                        <p class="mt-2 text-gray-500">No purchase orders found</p>
                        <x-mary-button label="Create First Purchase Order"
                            wire:click="createPurchaseOrder({{ $selectedSupplier->id }})" class="mt-4 btn-primary" />
                    </div>
                @endif
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Close" wire:click="$set('showOrdersModal', false)" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Manage Products Modal (existing functionality) --}}
    <x-mary-modal wire:model="showProductsModal" title="Manage Products - {{ $selectedSupplier?->name }}"
        box-class="max-w-6xl">
        @if ($selectedSupplier)
            <div class="space-y-6">
                {{-- Add Product Form --}}
                <div class="p-4 rounded-lg bg-base-100 border">
                    <h4 class="mb-4 font-semibold">Add Product to Supplier</h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <x-mary-select label="Product" :options="$products" wire:model="selectedProduct"
                            option-value="id" option-label="name" placeholder="Select a product" />
                        <x-mary-input label="Supplier SKU" wire:model="supplier_sku" placeholder="Supplier's SKU" />
                        <x-mary-input label="Part Number" wire:model="supplier_part_number"
                            placeholder="Supplier's part number" />
                    </div>
                    <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-4">
                        <x-mary-input label="Supplier Price" wire:model="supplier_price" type="number"
                            step="0.01" min="0" placeholder="0.00" />
                        <x-mary-input label="Min Order Qty" wire:model="minimum_order_quantity" type="number"
                            min="1" placeholder="1" />
                        <x-mary-input label="Lead Time (Days)" wire:model="product_lead_time_days" type="number"
                            min="1" placeholder="Auto from supplier" />
                        <x-mary-checkbox label="Preferred Supplier" wire:model="is_preferred" />
                    </div>
                    <div class="mt-4">
                        <x-mary-button label="Add Product" wire:click="addProduct" class="btn-primary" />
                    </div>
                </div>

                {{-- Products List --}}
                <div>
                    <h4 class="mb-3 font-semibold">Associated Products ({{ count($supplierProducts) }})</h4>
                    @if (count($supplierProducts) > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-zebra table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Supplier SKU</th>
                                        <th>Part Number</th>
                                        <th>Price</th>
                                        <th>Min Qty</th>
                                        <th>Lead Time</th>
                                        <th>Preferred</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($supplierProducts as $item)
                                        <tr>
                                            <td>
                                                <div class="font-medium">{{ $item['product']['name'] }}</div>
                                                <div class="text-sm text-gray-500">{{ $item['product']['sku'] }}</div>
                                            </td>
                                            <td>{{ $item['supplier_sku'] ?: '-' }}</td>
                                            <td>{{ $item['supplier_part_number'] ?: '-' }}</td>
                                            <td>₱{{ number_format($item['supplier_price'], 2) }}</td>
                                            <td>{{ $item['minimum_order_quantity'] ?: '-' }}</td>
                                            <td>{{ $item['lead_time_days'] ?: '-' }} days</td>
                                            <td>
                                                @if ($item['is_preferred'])
                                                    <x-mary-badge value="Yes" class="badge-success" />
                                                @else
                                                    <x-mary-button label="Set"
                                                        wire:click="togglePreferred({{ $item['id'] }})"
                                                        class="btn-ghost btn-xs" />
                                                @endif
                                            </td>
                                            <td>
                                                <div class="flex gap-1">
                                                    @if ($item['is_preferred'])
                                                        <x-mary-button icon="o-star"
                                                            wire:click="togglePreferred({{ $item['id'] }})"
                                                            class="btn-ghost btn-xs text-warning"
                                                            tooltip="Remove as Preferred" />
                                                    @endif
                                                    <x-mary-button icon="o-trash"
                                                        wire:click="removeSupplierProduct({{ $item['id'] }})"
                                                        wire:confirm="Remove this product from supplier?"
                                                        class="btn-ghost btn-xs text-error"
                                                        tooltip="Remove Product" />
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-8 text-center border-2 border-gray-300 border-dashed rounded-lg">
                            <x-heroicon-o-cube class="w-12 h-12 mx-auto text-gray-400" />
                            <p class="mt-2 text-gray-500">No products associated with this supplier</p>
                            <p class="text-sm text-gray-400">Add products using the form above</p>
                        </div>
                    @endif
                </div>

                {{-- Additional Options --}}
                <div class="p-4 rounded-lg bg-info/5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h5 class="font-medium">Bulk Operations</h5>
                            <p class="text-sm text-gray-600">Perform actions on multiple products</p>
                        </div>
                        <div class="flex gap-2">
                            <x-mary-button label="Import Products" class="btn-outline btn-sm" />
                            <x-mary-button label="Export List" class="btn-outline btn-sm" />
                        </div>
                    </div>
                </div>
            </div>

            <x-slot:actions>
                <x-mary-button label="Close" wire:click="$set('showProductsModal', false)" class="btn-primary" />
            </x-slot:actions>
        @endif
    </x-mary-modal>
</div>
