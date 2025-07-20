<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Service Management</h1>
            <p class="text-base-content/70">Manage labor services, diagnostics, and consultation services</p>
        </div>
        <x-mary-button wire:click="openCreateModal" icon="o-plus" class="btn-primary">
            Add New Service
        </x-mary-button>
    </div>

    {{-- Filters --}}
    <div class="p-4 border rounded-lg bg-base-100 border-base-300">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-mary-input wire:model.live.debounce.300ms="search" placeholder="Search services..."
                icon="o-magnifying-glass" />

            <x-mary-select wire:model.live="statusFilter" placeholder="All Status" :options="[
                ['id' => '', 'name' => 'All Status'],
                ['id' => 'active', 'name' => 'Active'],
                ['id' => 'inactive', 'name' => 'Inactive'],
            ]" />
        </div>

        <div class="mt-4">
            <x-mary-button wire:click="clearFilters" icon="o-x-mark" class="btn-sm btn-ghost">
                Clear Filters
            </x-mary-button>
        </div>
    </div>

    {{-- Services Table --}}
    <div class="overflow-x-auto border rounded-lg bg-base-100 border-base-300">
        <table class="table w-full table-zebra">
            <thead>
                <tr>
                    <th>Service Details</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    <tr>
                        <td>
                            <div>
                                <div class="font-semibold">{{ $service->name }}</div>
                                <div class="text-sm text-base-content/60">{{ $service->code }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="font-semibold text-primary">{{ $service->formatted_price }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $service->status === 'active' ? 'badge-success' : 'badge-error' }}">
                                {{ ucfirst($service->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <x-mary-button wire:click="openEditModal({{ $service->id }})" icon="o-pencil"
                                    class="btn-xs btn-ghost" tooltip="Edit" />
                                <x-mary-button wire:click="deleteService({{ $service->id }})" icon="o-trash"
                                    class="btn-xs btn-ghost text-error" tooltip="Delete"
                                    wire:confirm="Are you sure you want to delete this service?" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-base-content/60">
                            No services found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $services->links() }}
    </div>

    {{-- Service Modal --}}
    <x-mary-modal wire:model="showModal" :title="$editingService ? 'Edit Service' : 'Add New Service'" box-class="w-11/12 max-w-lg">
        <form wire:submit="saveService" class="space-y-4">
            <x-mary-input wire:model="name" label="Service Name" required placeholder="e.g., Oil Change Labor" />

            <x-mary-input wire:model="price" label="Price (₱)" type="number" step="0.01" min="0" required />

            <x-mary-select wire:model="status" label="Status" required :options="[['id' => 'active', 'name' => 'Active'], ['id' => 'inactive', 'name' => 'Inactive']]" />

            <x-mary-textarea wire:model="notes" label="Internal Notes" placeholder="Internal notes for staff..."
                rows="3" />
        </form>

        <x-slot:actions>
            <x-mary-button wire:click="showModal = false">Cancel</x-mary-button>
            <x-mary-button wire:click="saveService" class="btn-primary">
                {{ $editingService ? 'Update' : 'Create' }} Service
            </x-mary-button>
        </x-slot:actions>
    </x-mary-modal>
</div>
