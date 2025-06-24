<div>
    <x-mary-header title="Inventory Locations" separator>
        <x-slot:actions>
            <x-mary-button label="Migrate Legacy" wire:click="migrateLocations" class="btn-warning" />
            <x-mary-button label="Bulk Create" wire:click="$set('showBulkModal', true)" class="btn-outline" />
            <x-mary-button label="Add Location" wire:click="openModal" class="btn-primary" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <x-mary-input label="Search" wire:model.live="search" placeholder="Code, name, or description..." />
        <x-mary-select label="Zone" wire:model.live="zoneFilter" :options="$zones->map(fn($z) => ['value' => $z, 'label' => $z])" placeholder="All Zones" />
        <x-mary-select label="Status" wire:model.live="statusFilter" :options="[
            ['value' => '', 'label' => 'All Status'],
            ['value' => '1', 'label' => 'Active'],
            ['value' => '0', 'label' => 'Inactive'],
        ]" />
    </div>

    {{-- Locations Table --}}
    <x-mary-table :headers="[
        ['key' => 'code', 'label' => 'Code'],
        ['key' => 'name', 'label' => 'Name'],
        ['key' => 'zone', 'label' => 'Zone'],
        ['key' => 'section', 'label' => 'Section'],
        ['key' => 'level', 'label' => 'Level'],
        ['key' => 'inventories_count', 'label' => 'Items'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'actions', 'label' => 'Actions'],
    ]" :rows="$locations" with-pagination>

        @scope('cell_status', $location)
            <x-mary-badge value="{{ $location->is_active ? 'Active' : 'Inactive' }}"
                class="badge-{{ $location->is_active ? 'success' : 'error' }} badge-sm" />
        @endscope

        @scope('cell_inventories_count', $location)
            @if ($location->inventories_count > 0)
                <x-mary-badge value="{{ $location->inventories_count }}" class="badge-info badge-sm" />
            @else
                <span class="text-gray-400">0</span>
            @endif
        @endscope

        @scope('cell_actions', $location)
            <div class="flex gap-2">
                <x-mary-button wire:click="editLocation({{ $location->id }})" class="btn-sm btn-outline" icon="o-pencil" />
                @if ($location->inventories_count === 0)
                    <x-mary-button wire:click="deleteLocation({{ $location->id }})" class="btn-sm btn-error" icon="o-trash"
                        onclick="confirm('Are you sure?') || event.stopImmediatePropagation()" />
                @endif
            </div>
        @endscope
    </x-mary-table>

    {{-- Add/Edit Modal --}}
    <x-mary-modal wire:model="showModal" title="{{ $editMode ? 'Edit' : 'Add' }} Location">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-mary-input label="Code" wire:model="code" placeholder="e.g., A1-B2" />
            <x-mary-input label="Name" wire:model="name" placeholder="e.g., Aisle A, Shelf 1" />
            <x-mary-input label="Zone" wire:model="zone" placeholder="e.g., A, STORAGE" />
            <x-mary-input label="Section" wire:model="section" placeholder="e.g., 1, MAIN" />
            <x-mary-input label="Level" wire:model="level" placeholder="e.g., TOP, MIDDLE" />
            <x-mary-input label="Sort Order" wire:model="sort_order" type="number" placeholder="0" />
        </div>
        <x-mary-textarea label="Description" wire:model="description" rows="2" />
        <x-mary-checkbox label="Active" wire:model="is_active" />

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showModal', false)" />
            <x-mary-button label="Save" wire:click="save" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Bulk Create Modal --}}
    <x-mary-modal wire:model="showBulkModal" title="Bulk Create Locations">
        <div class="space-y-4">
            <x-mary-input label="Zone" wire:model="bulkZone" placeholder="e.g., A, STORAGE" />
            <x-mary-input label="Prefix" wire:model="bulkPrefix" placeholder="e.g., SHELF, BIN" />
            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="Start Number" wire:model="bulkStart" type="number" />
                <x-mary-input label="End Number" wire:model="bulkEnd" type="number" />
            </div>
            <div class="text-sm text-gray-600">
                This will create locations like: {{ strtoupper($bulkPrefix ?: 'PREFIX') }}-001,
                {{ strtoupper($bulkPrefix ?: 'PREFIX') }}-002, etc.
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showBulkModal', false)" />
            <x-mary-button label="Create" wire:click="createBulkLocations" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>
