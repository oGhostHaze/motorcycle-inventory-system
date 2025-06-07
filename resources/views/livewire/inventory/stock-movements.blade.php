<div>
    {{-- Page Header --}}
    <x-mary-header title="Stock Movements" subtitle="Track all inventory movements and transactions" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search products..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-path" wire:click="$refresh" class="btn-ghost" tooltip="Refresh" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-6">
        <x-mary-select placeholder="All Products" :options="$filterOptions['products']" wire:model.live="productFilter" option-value="value"
            option-label="label" />
        <x-mary-select placeholder="All Warehouses" :options="$filterOptions['warehouses']" wire:model.live="warehouseFilter"
            option-value="value" option-label="label" />
        <x-mary-select placeholder="All Users" :options="$filterOptions['users']" wire:model.live="userFilter" option-value="value"
            option-label="label" />
        <x-mary-select placeholder="All Types" :options="$filterOptions['types']" wire:model.live="typeFilter" option-value="value"
            option-label="label" />
        <x-mary-select placeholder="All Dates" :options="$filterOptions['dates']" wire:model.live="dateFilter" option-value="value"
            option-label="label" />
        <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
            Clear Filters
        </x-mary-button>
    </div>

    {{-- Movements Table --}}
    <x-mary-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Before/After</th>
                        <th>User</th>
                        <th>Reference</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>
                                <div class="text-sm">
                                    <div class="font-medium">{{ $movement->created_at->format('M d, Y') }}</div>
                                    <div class="text-gray-500">{{ $movement->created_at->format('H:i:s') }}</div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="font-medium">{{ $movement->product->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $movement->product->sku }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $movement->warehouse->name }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <x-mary-badge value="{{ ucfirst(str_replace('_', ' ', $movement->type)) }}"
                                        class="badge-{{ $this->getMovementTypeClass($movement->type) }} badge-sm" />
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span
                                        class="font-bold text-lg {{ $movement->quantity_changed > 0 ? 'text-success' : 'text-error' }}">
                                        {{ $movement->quantity_changed > 0 ? '+' : '' }}{{ number_format($movement->quantity_changed) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="text-xs text-center">
                                    <div>{{ number_format($movement->quantity_before) }}</div>
                                    <div class="text-gray-400">→</div>
                                    <div class="font-semibold">{{ number_format($movement->quantity_after) }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $movement->user->name ?? 'System' }}</span>
                            </td>
                            <td>
                                @if ($movement->reference)
                                    <div class="text-xs">
                                        <div class="font-medium">{{ class_basename($movement->reference_type) }}</div>
                                        <div class="text-gray-500">#{{ $movement->reference_id }}</div>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($movement->notes)
                                    <div class="max-w-xs text-xs truncate" title="{{ $movement->notes }}">
                                        {{ $movement->notes }}
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="py-8">
                                    <x-heroicon-o-arrow-path class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-gray-500">No stock movements found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $movements->links() }}
        </div>
    </x-mary-card>
</div>
