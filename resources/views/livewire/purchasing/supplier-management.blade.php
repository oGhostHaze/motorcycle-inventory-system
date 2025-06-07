<div>
    {{-- Page Header --}}
    <x-mary-header title="Supplier Management" subtitle="Manage suppliers and product relationships" separator>
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
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <x-mary-select placeholder="All Countries" :options="$filterOptions['countries']" wire:model.live="countryFilter"
            option-value="value" option-label="label" />
        <x-mary-select placeholder="All Status" :options="$filterOptions['statuses']" wire:model.live="statusFilter" option-value="value"
            option-label="label" />
        <x-mary-select placeholder="All Ratings" :options="$filterOptions['ratings']" wire:model.live="ratingFilter" option-value="value"
            option-label="label" />
        <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
            Clear Filters
        </x-mary-button>
    </div>

    {{-- Suppliers Grid --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($suppliers as $supplier)
            <x-mary-card class="h-full">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="p-3 rounded-lg {{ $supplier->is_active ? 'bg-primary/10' : 'bg-gray-300' }}">
                            <x-heroicon-o-building-office-2
                                class="w-8 h-8 {{ $supplier->is_active ? 'text-primary' : 'text-gray-500' }}" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">{{ $supplier->name }}</h3>
                            <x-mary-badge value="{{ $supplier->is_active ? 'Active' : 'Inactive' }}"
                                class="badge-{{ $supplier->is_active ? 'success' : 'error' }} badge-sm" />
                        </div>
                    </div>

                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                            <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                        </div>
                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                            <li><a wire:click="editSupplier({{ $supplier->id }})">
                                    <x-heroicon-o-pencil class="w-4 h-4" /> Edit</a></li>
                            <li><a wire:click="openProductsModal({{ $supplier->id }})">
                                    <x-heroicon-o-cube class="w-4 h-4" /> Manage Products</a></li>
                            <li><a wire:click="toggleStatus({{ $supplier->id }})">
                                    <x-heroicon-o-{{ $supplier->is_active ? 'x-mark' : 'check' }} class="w-4 h-4" />
                                    {{ $supplier->is_active ? 'Deactivate' : 'Activate' }}</a></li>
                            <li><a wire:click="deleteSupplier({{ $supplier->id }})" wire:confirm="Are you sure?"
                                    class="text-error">
                                    <x-heroicon-o-trash class="w-4 h-4" /> Delete</a></li>
                        </ul>
                    </div>
                </div>

                {{-- Contact Information --}}
                <div class="mb-4 space-y-2">
                    @if ($supplier->contact_person)
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-user class="w-4 h-4 text-gray-500" />
                            <span class="text-sm">{{ $supplier->contact_person }}</span>
                        </div>
                    @endif

                    @if ($supplier->email)
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-envelope class="w-4 h-4 text-gray-500" />
                            <span class="text-sm">{{ $supplier->email }}</span>
                        </div>
                    @endif

                    @if ($supplier->phone)
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-phone class="w-4 h-4 text-gray-500" />
                            <span class="text-sm">{{ $supplier->phone }}</span>
                        </div>
                    @endif

                    @if ($supplier->city || $supplier->country)
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-map-pin class="w-4 h-4 text-gray-500" />
                            <span
                                class="text-sm">{{ $supplier->city }}{{ $supplier->city && $supplier->country ? ', ' : '' }}{{ $supplier->country }}</span>
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
                    <div class="p-3 text-sm rounded-lg bg-base-200">
                        <div class="text-gray-600">{{ Str::limit($supplier->notes, 100) }}</div>
                    </div>
                @endif

                {{-- Quick Actions --}}
                <div class="flex gap-2 mt-4">
                    <x-mary-button label="Create PO" class="flex-1 btn-outline btn-sm" />
                    <x-mary-button label="View Orders" class="flex-1 btn-outline btn-sm" />
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
    <x-mary-modal wire:model="showModal" title="{{ $editMode ? 'Edit Supplier' : 'Create New Supplier' }}"
        subtitle="Manage supplier information and details">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            {{-- Basic Information --}}
            <div class="space-y-4 md:col-span-2">
                <h4 class="text-lg font-semibold">Basic Information</h4>
            </div>

            <x-mary-input label="Supplier Name" wire:model="name" placeholder="Enter supplier name" />
            <x-mary-input label="Contact Person" wire:model="contact_person" placeholder="Primary contact" />

            <x-mary-input label="Email Address" wire:model="email" placeholder="supplier@example.com" />
            <x-mary-input label="Phone Number" wire:model="phone" placeholder="Contact number" />

            {{-- Address Information --}}
            <div class="space-y-4 md:col-span-2">
                <h4 class="text-lg font-semibold">Address Information</h4>
            </div>

            <x-mary-textarea label="Address" wire:model="address" placeholder="Complete address" rows="2"
                class="md:col-span-2" />
            <x-mary-input label="City" wire:model="city" placeholder="City" />
            <x-mary-input label="Country" wire:model="country" placeholder="Country" />

            {{-- Performance Information --}}
            <div class="space-y-4 md:col-span-2">
                <h4 class="text-lg font-semibold">Performance Information</h4>
            </div>

            <x-mary-input label="Rating (1-5)" wire:model="rating" type="number" min="1" max="5"
                step="0.1" placeholder="0.0" />
            <x-mary-input label="Lead Time (Days)" wire:model="lead_time_days" type="number" min="1"
                placeholder="0" />

            <x-mary-textarea label="Notes" wire:model="notes" placeholder="Additional supplier notes"
                rows="3" class="md:col-span-2" />

            <div class="flex items-center md:col-span-2">
                <x-mary-checkbox label="Active Supplier" wire:model="is_active" />
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showModal', false)" />
            <x-mary-button label="{{ $editMode ? 'Update Supplier' : 'Create Supplier' }}" wire:click="save"
                class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Supplier Products Modal --}}
    <x-mary-modal wire:model="showProductsModal" title="Manage Supplier Products"
        subtitle="Associate products with {{ $selectedSupplier?->name }}">

        @if ($selectedSupplier)
            <div class="space-y-6">
                {{-- Add New Product --}}
                <div class="p-4 border rounded-lg bg-primary/5">
                    <h4 class="mb-4 font-semibold">Add Product</h4>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-6">
                        <x-mary-select label="Product" :options="$products->map(fn($p) => ['value' => $p->id, 'label' => $p->name])" wire:model="selectedProduct"
                            placeholder="Select product" />

                        <x-mary-input label="Supplier SKU" wire:model="supplier_sku" placeholder="SKU" />

                        <x-mary-input label="Part Number" wire:model="supplier_part_number" placeholder="Part #" />

                        <x-mary-input label="Price" wire:model="supplier_price" type="number" step="0.01"
                            placeholder="0.00" />

                        <x-mary-input label="Min Order" wire:model="minimum_order_quantity" type="number"
                            placeholder="1" />

                        <div class="flex items-end">
                            <x-mary-button label="Add Product" wire:click="addProduct"
                                class="w-full btn-primary btn-sm" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <x-mary-checkbox label="Preferred Supplier for this product" wire:model="is_preferred" />
                    </div>
                </div>

                {{-- Current Products --}}
                <div>
                    <h4 class="mb-4 font-semibold">Current Products ({{ count($supplierProducts) }})</h4>
                    @if (count($supplierProducts) > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-zebra table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Supplier SKU</th>
                                        <th>Part Number</th>
                                        <th>Price</th>
                                        <th>Min Order</th>
                                        <th>Lead Time</th>
                                        <th>Preferred</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($supplierProducts as $supplierProduct)
                                        <tr>
                                            <td>
                                                <div class="font-medium">
                                                    {{ $supplierProduct['product']['name'] ?? 'Unknown Product' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $supplierProduct['product']['sku'] ?? '' }}</div>
                                            </td>
                                            <td>{{ $supplierProduct['supplier_sku'] ?? '-' }}</td>
                                            <td>{{ $supplierProduct['supplier_part_number'] ?? '-' }}</td>
                                            <td>₱{{ number_format($supplierProduct['supplier_price'] ?? 0, 2) }}</td>
                                            <td class="text-center">
                                                {{ $supplierProduct['minimum_order_quantity'] ?? 1 }}</td>
                                            <td class="text-center">{{ $supplierProduct['lead_time_days'] ?? '-' }}
                                                days</td>
                                            <td class="text-center">
                                                <x-mary-button
                                                    icon="o-{{ $supplierProduct['is_preferred'] ? 'star' : 'star' }}"
                                                    wire:click="togglePreferred({{ $supplierProduct['id'] }})"
                                                    class="btn-ghost btn-xs {{ $supplierProduct['is_preferred'] ? 'text-yellow-500' : 'text-gray-400' }}"
                                                    tooltip="{{ $supplierProduct['is_preferred'] ? 'Preferred Supplier' : 'Set as Preferred' }}" />
                                            </td>
                                            <td>
                                                <div class="flex gap-1">
                                                    <x-mary-button icon="o-pencil" class="btn-ghost btn-xs text-info"
                                                        tooltip="Edit Product Details" />
                                                    <x-mary-button icon="o-trash"
                                                        wire:click="removeSupplierProduct({{ $supplierProduct['id'] }})"
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
