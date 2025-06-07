<div>
    {{-- Page Header --}}
    <x-mary-header title="Warehouse Management" subtitle="Manage warehouse locations and facilities" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search warehouses..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" @click="$wire.openModal()">
                Add Warehouse
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <x-mary-select placeholder="Filter by type" :options="$typeOptions" wire:model.live="typeFilter" />
        <x-mary-select placeholder="Filter by status" :options="$statusOptions" wire:model.live="statusFilter" />
        <div class="md:col-span-2 md:flex md:justify-end">
            <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
                Clear Filters
            </x-mary-button>
        </div>
    </div>

    {{-- Warehouses Grid --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($warehouses as $warehouse)
            <x-mary-card class="h-full">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="p-3 rounded-lg {{ $warehouse->is_active ? 'bg-primary/10' : 'bg-gray-300' }}">
                            <x-heroicon-o-building-office
                                class="w-8 h-8 {{ $warehouse->is_active ? 'text-primary' : 'text-gray-500' }}" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">{{ $warehouse->name }}</h3>
                            <div class="flex items-center gap-2">
                                <x-mary-badge value="{{ $warehouse->code }}" class="badge-neutral badge-sm" />
                                <x-mary-badge value="{{ ucfirst($warehouse->type) }}"
                                    class="badge-{{ $warehouse->type === 'main' ? 'primary' : ($warehouse->type === 'retail' ? 'secondary' : 'accent') }} badge-sm" />
                            </div>
                        </div>
                    </div>

                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                            <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                        </div>
                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                            <li><a wire:click="editWarehouse({{ $warehouse->id }})"><x-heroicon-o-pencil
                                        class="w-4 h-4" /> Edit</a></li>
                            <li><a wire:click="toggleStatus({{ $warehouse->id }})">
                                    <x-heroicon-o-{{ $warehouse->is_active ? 'x-mark' : 'check' }} class="w-4 h-4" />
                                    {{ $warehouse->is_active ? 'Deactivate' : 'Activate' }}
                                </a></li>
                            <li><a wire:click="deleteWarehouse({{ $warehouse->id }})" wire:confirm="Are you sure?"
                                    class="text-error">
                                    <x-heroicon-o-trash class="w-4 h-4" /> Delete
                                </a></li>
                        </ul>
                    </div>
                </div>

                {{-- Status Badge --}}
                <div class="mb-4">
                    <x-mary-badge value="{{ $warehouse->is_active ? 'Active' : 'Inactive' }}"
                        class="badge-{{ $warehouse->is_active ? 'success' : 'error' }}" />
                </div>

                {{-- Address Information --}}
                <div class="mb-4 space-y-2">
                    <div class="flex items-start space-x-2">
                        <x-heroicon-o-map-pin class="w-4 h-4 mt-0.5 text-gray-500" />
                        <div class="text-sm">
                            <div>{{ $warehouse->address }}</div>
                            <div class="text-gray-600">{{ $warehouse->city }}</div>
                        </div>
                    </div>

                    @if ($warehouse->manager_name)
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-user class="w-4 h-4 text-gray-500" />
                            <span class="text-sm">{{ $warehouse->manager_name }}</span>
                        </div>
                    @endif

                    @if ($warehouse->phone)
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-phone class="w-4 h-4 text-gray-500" />
                            <span class="text-sm">{{ $warehouse->phone }}</span>
                        </div>
                    @endif
                </div>

                {{-- Statistics --}}
                <div class="grid grid-cols-3 gap-2">
                    <div class="p-2 text-center rounded bg-info/10">
                        <div class="text-lg font-bold text-info">{{ $warehouse->inventory_count }}</div>
                        <div class="text-xs text-gray-600">Items</div>
                    </div>
                    <div class="p-2 text-center rounded bg-success/10">
                        <div class="text-lg font-bold text-success">{{ $warehouse->sales_count }}</div>
                        <div class="text-xs text-gray-600">Sales</div>
                    </div>
                    <div class="p-2 text-center rounded bg-warning/10">
                        <div class="text-lg font-bold text-warning">{{ $warehouse->purchase_orders_count }}</div>
                        <div class="text-xs text-gray-600">POs</div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="flex gap-2 mt-4">
                    <x-mary-button label="View Inventory" class="flex-1 btn-outline btn-sm" link="#" />
                    <x-mary-button label="Stock Transfer" class="flex-1 btn-outline btn-sm" link="#" />
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
    <x-mary-modal wire:model="showModal" title="{{ $editMode ? 'Edit Warehouse' : 'Create New Warehouse' }}"
        subtitle="Manage warehouse information and settings" class="w-11/12 max-w-2xl">

        <div class="space-y-4">
            {{-- Basic Information --}}
            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="Warehouse Name" wire:model="name" placeholder="Enter warehouse name" />
                <div>
                    <x-mary-input label="Warehouse Code" wire:model="code" placeholder="e.g., MAIN01">
                        <x-slot:append>
                            <x-mary-button icon="o-sparkles" wire:click="generateCode" class="btn-outline btn-sm"
                                tooltip="Generate Code" />
                        </x-slot:append>
                    </x-mary-input>
                </div>
            </div>

            {{-- Location Information --}}
            <x-mary-textarea label="Address" wire:model="address" placeholder="Complete warehouse address"
                rows="2" />

            <x-mary-input label="City" wire:model="city" placeholder="City location" />

            {{-- Contact Information --}}
            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="Manager Name" wire:model="manager_name" placeholder="Warehouse manager" />
                <x-mary-input label="Phone Number" wire:model="phone" placeholder="Contact number" />
            </div>

            {{-- Type and Status --}}
            <div class="grid grid-cols-2 gap-4">
                <x-mary-select label="Warehouse Type" :options="[
                    ['value' => 'main', 'label' => 'Main Warehouse'],
                    ['value' => 'retail', 'label' => 'Retail Store'],
                    ['value' => 'storage', 'label' => 'Storage Facility'],
                    ['value' => 'overflow', 'label' => 'Overflow Storage'],
                ]" wire:model="type" />

                <div class="flex items-center pt-8">
                    <x-mary-checkbox label="Active Warehouse" wire:model="is_active" />
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showModal', false)" />
            <x-mary-button label="{{ $editMode ? 'Update Warehouse' : 'Create Warehouse' }}" wire:click="save"
                class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>
