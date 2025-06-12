<div>
    {{-- Header Section --}}
    <div class="mb-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Warehouse Management</h1>
                <p class="text-gray-600">Manage your warehouse locations and facilities</p>
            </div>
            <x-mary-button label="Add Warehouse" wire:click="openModal" class="btn-primary" icon="o-plus" />
        </div>
    </div>

    {{-- Search and Filters --}}
    <x-mary-card class="mb-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <x-mary-input label="Search warehouses..." wire:model.live.debounce.300ms="search" icon="o-magnifying-glass"
                placeholder="Name, code, or city" />

            <x-mary-select label="Type" :options="$typeOptions" wire:model.live="typeFilter" option-value="value"
                option-label="label" />

            <x-mary-select label="Status" :options="$statusOptions" wire:model.live="statusFilter" option-value="value"
                option-label="label" />

            <div class="flex items-end">
                <x-mary-button label="Clear Filters"
                    wire:click="$set('search', ''); $set('typeFilter', ''); $set('statusFilter', '')"
                    class="btn-outline btn-sm" />
            </div>
        </div>
    </x-mary-card>

    {{-- Warehouses Grid --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($warehouses as $warehouse)
            <x-mary-card class="transition-shadow group hover:shadow-lg">
                {{-- Header --}}
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-semibold">{{ $warehouse->name }}</h3>
                            <x-mary-badge value="{{ strtoupper($warehouse->code) }}" class="badge-outline badge-sm" />
                        </div>
                        <div class="flex items-center gap-1 text-sm text-gray-600">
                            <x-heroicon-o-map-pin class="w-4 h-4" />
                            <span>{{ $warehouse->city }}</span>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <x-mary-badge value="{{ $warehouse->is_active ? 'Active' : 'Inactive' }}"
                        class="{{ $warehouse->is_active ? 'badge-success' : 'badge-error' }}" />
                </div>

                {{-- Warehouse Info --}}
                <div class="mb-4 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Type:</span>
                        <span class="capitalize">{{ str_replace('_', ' ', $warehouse->type) }}</span>
                    </div>
                    @if ($warehouse->manager_name)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Manager:</span>
                            <span>{{ $warehouse->manager_name }}</span>
                        </div>
                    @endif
                    @if ($warehouse->phone)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Phone:</span>
                            <span>{{ $warehouse->phone }}</span>
                        </div>
                    @endif
                </div>

                {{-- Stats --}}
                <div class="grid grid-cols-3 gap-2 p-3 mb-4 rounded-lg bg-base-200">
                    <div class="text-center">
                        <div class="text-lg font-bold text-info">{{ $warehouse->inventory_count }}</div>
                        <div class="text-xs text-gray-600">Products</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-warning">{{ $warehouse->sales_count }}</div>
                        <div class="text-xs text-gray-600">Sales</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-success">{{ $warehouse->purchase_orders_count }}</div>
                        <div class="text-xs text-gray-600">POs</div>
                    </div>
                </div>

                {{-- Address --}}
                <div class="mb-4">
                    <p class="text-sm text-gray-600 line-clamp-2">{{ $warehouse->address }}</p>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 mb-4">
                    <x-mary-button label="Edit" wire:click="editWarehouse({{ $warehouse->id }})"
                        class="flex-1 btn-outline btn-sm" icon="o-pencil" />

                    <x-mary-button label="Delete" wire:click="deleteWarehouse({{ $warehouse->id }})"
                        wire:confirm="Are you sure you want to delete this warehouse?"
                        class="btn-outline btn-error btn-sm" icon="o-trash" />
                </div>

                {{-- Quick Actions --}}
                <div class="flex gap-2 mt-4">
                    <x-mary-button label="View Inventory" wire:click="viewInventory({{ $warehouse->id }})"
                        class="flex-1 btn-outline btn-sm" icon="o-cube" />

                    <x-mary-button label="Stock Transfer" wire:click="openStockTransfer({{ $warehouse->id }})"
                        class="flex-1 btn-outline btn-sm" icon="o-arrow-path" />
                </div>
            </x-mary-card>
        @empty
            <div class="col-span-full">
                <x-mary-card>
                    <div class="py-8 text-center">
                        <x-heroicon-o-building-office class="w-12 h-12 mx-auto text-gray-400" />
                        <p class="mt-2 text-gray-500">No warehouses found</p>
                        <x-mary-button label="Create First Warehouse" wire:click="openModal" class="mt-4 btn-primary" />
                    </div>
                </x-mary-card>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $warehouses->links() }}
    </div>

    {{-- Create/Edit Modal --}}
    <x-mary-modal wire:model="showModal" title="{{ $editMode ? 'Edit Warehouse' : 'Create Warehouse' }}"
        box-class="max-w-2xl">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="Warehouse Name *" wire:model="name" />
                <x-mary-input label="Code *" wire:model="code" placeholder="e.g., WH01" />
            </div>

            <x-mary-textarea label="Address *" wire:model="address" rows="2" />

            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="City *" wire:model="city" />
                <x-mary-select label="Type *" :options="$typeOptions" wire:model="type" option-value="value"
                    option-label="label" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="Manager Name" wire:model="manager_name" />
                <x-mary-input label="Phone" wire:model="phone" />
            </div>

            <x-mary-checkbox label="Active" wire:model="is_active" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showModal', false)" />
            <x-mary-button label="{{ $editMode ? 'Update' : 'Create' }}" wire:click="save" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- View Inventory Modal --}}
    <x-mary-modal wire:model="showInventoryModal" title="Inventory - {{ $inventoryWarehouse?->name }}"
        box-class="max-w-6xl">

        @if ($inventoryWarehouse)
            <div class="space-y-4">
                {{-- Search --}}
                <x-mary-input label="Search Products" wire:model.live.debounce.300ms="inventorySearch"
                    icon="o-magnifying-glass" placeholder="Search by product name or SKU" />

                {{-- Inventory Summary --}}
                <div class="grid grid-cols-3 gap-4 p-4 rounded-lg bg-base-200">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-info">{{ count($inventoryData) }}</div>
                        <div class="text-sm text-gray-600">Product Lines</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-success">
                            {{ number_format(collect($inventoryData)->sum('quantity_on_hand')) }}
                        </div>
                        <div class="text-sm text-gray-600">Total Units</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary">
                            ₱{{ number_format(collect($inventoryData)->sum(fn($item) => $item->quantity_on_hand * $item->product->cost_price), 2) }}
                        </div>
                        <div class="text-sm text-gray-600">Total Value</div>
                    </div>
                </div>

                {{-- Inventory Table --}}
                @if (count($inventoryData) > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th>On Hand</th>
                                    <th>Reserved</th>
                                    <th>Available</th>
                                    <th>Location</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventoryData as $inventory)
                                    <tr>
                                        <td>
                                            <div class="font-medium">{{ $inventory->product->name }}</div>
                                            @if ($inventory->product->brand)
                                                <div class="text-xs text-gray-500">
                                                    {{ $inventory->product->brand->name }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $inventory->product->sku }}</td>
                                        <td>{{ $inventory->product->category->name ?? 'N/A' }}</td>
                                        <td class="font-semibold">{{ number_format($inventory->quantity_on_hand) }}
                                        </td>
                                        <td class="text-warning">{{ number_format($inventory->quantity_reserved) }}
                                        </td>
                                        <td class="font-semibold text-success">
                                            {{ number_format($inventory->quantity_available) }}</td>
                                        <td>{{ $inventory->location ?: 'Not set' }}</td>
                                        <td class="font-medium">
                                            ₱{{ number_format($inventory->quantity_on_hand * $inventory->product->cost_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-cube class="w-12 h-12 mx-auto text-gray-400" />
                        <p class="mt-2 text-gray-500">No inventory found</p>
                        @if ($inventorySearch)
                            <p class="text-sm text-gray-400">Try adjusting your search terms</p>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Close" wire:click="closeInventoryModal" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Stock Transfer Modal --}}
    <x-mary-modal wire:model="showTransferModal" title="Stock Transfer from {{ $transferFromWarehouse?->name }}"
        box-class="max-w-2xl">

        @if ($transferFromWarehouse)
            <div class="space-y-4">
                {{-- Source Info --}}
                <div class="p-4 rounded-lg bg-info/10">
                    <div class="flex items-center gap-2 mb-2">
                        <x-heroicon-o-building-office class="w-5 h-5 text-info" />
                        <span class="font-medium">Transfer From:</span>
                    </div>
                    <div class="text-lg font-semibold">{{ $transferFromWarehouse->name }}</div>
                    <div class="text-sm text-gray-600">{{ $transferFromWarehouse->address }},
                        {{ $transferFromWarehouse->city }}</div>
                </div>

                {{-- Transfer Form --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-mary-select label="Destination Warehouse *" :options="$this->getDestinationWarehouses()" wire:model="transferToWarehouse"
                        option-value="value" option-label="label" placeholder="Select destination..." />

                    <x-mary-select label="Product *" :options="$this->getAvailableProducts()" wire:model.live="transferProduct"
                        option-value="value" option-label="label" placeholder="Select product..." />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-mary-input label="Quantity *" wire:model.live="transferQuantity" type="number"
                        min="1" max="{{ $this->getMaxTransferQuantity() }}" placeholder="Enter quantity" />

                    @if ($transferProduct)
                        <div class="flex items-end">
                            <div class="text-sm text-gray-600">
                                Max available: <span
                                    class="font-medium">{{ number_format($this->getMaxTransferQuantity()) }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <x-mary-textarea label="Transfer Notes" wire:model="transferNotes"
                    placeholder="Reason for transfer, special instructions, etc." rows="3" />

                {{-- Transfer Summary --}}
                @if ($transferProduct && $transferQuantity)
                    <div class="p-4 border rounded-lg bg-success/10 border-success/20">
                        <div class="flex items-center gap-2 mb-2">
                            <x-heroicon-o-arrow-path class="w-5 h-5 text-success" />
                            <span class="font-medium text-success">Transfer Summary</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Product:</span>
                                <span class="font-medium">
                                    {{ collect($this->getAvailableProducts())->firstWhere('value', $transferProduct)['label'] ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span>Quantity:</span>
                                <span class="font-medium">{{ number_format($transferQuantity) }} units</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Destination:</span>
                                <span class="font-medium">
                                    {{ collect($this->getDestinationWarehouses())->firstWhere('value', $transferToWarehouse)['label'] ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="closeTransferModal" />
            <x-mary-button label="Transfer Stock" wire:click="processStockTransfer" class="btn-success"
                :disabled="!$transferToWarehouse || !$transferProduct || !$transferQuantity" />
        </x-slot:actions>
    </x-mary-modal>
</div>
