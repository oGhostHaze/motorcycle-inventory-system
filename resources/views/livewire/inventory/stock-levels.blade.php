<div>
    {{-- Page Header --}}
    <x-mary-header title="Stock Levels" subtitle="Monitor inventory levels across all warehouses" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search products..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-path" wire:click="refreshData" class="btn-ghost" tooltip="Refresh Data" />
            <div class="btn-group">
                <x-mary-button icon="o-squares-2x2" wire:click="setViewMode('grid')"
                    class="btn-sm {{ $viewMode === 'grid' ? 'btn-active' : 'btn-ghost' }}" tooltip="Grid View" />
                <x-mary-button icon="o-list-bullet" wire:click="setViewMode('table')"
                    class="btn-sm {{ $viewMode === 'table' ? 'btn-active' : 'btn-ghost' }}" tooltip="Table View" />
            </div>
        </x-slot:actions>
    </x-mary-header>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-4">
        <x-mary-stat title="Total Items" description="Items in stock" value="{{ number_format($totalItems) }}"
            icon="o-cube" color="text-info" />

        <x-mary-stat title="Total Value" description="Inventory worth" value="₱{{ number_format($totalValue, 2) }}"
            icon="o-banknotes" color="text-success" />

        <x-mary-stat title="Low Stock" description="Items need reorder" value="{{ $lowStockCount }}"
            icon="o-exclamation-triangle" color="text-warning" />

        <x-mary-stat title="Out of Stock" description="Zero quantity items" value="{{ $outOfStockCount }}"
            icon="o-x-circle" color="text-error" />
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-5">
        <x-mary-select placeholder="All Warehouses" :options="$filterOptions['warehouses']" wire:model.live="warehouseFilter" />
        <x-mary-select placeholder="All Categories" :options="$filterOptions['categories']" wire:model.live="categoryFilter" />
        <x-mary-select placeholder="Stock Status" :options="$filterOptions['stock']" wire:model.live="stockFilter" />
        <x-mary-select placeholder="Product Status" :options="$filterOptions['status']" wire:model.live="statusFilter" />
        <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
            Clear Filters
        </x-mary-button>
    </div>

    {{-- Content based on view mode --}}
    @if ($viewMode === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($inventory as $item)
                <x-mary-card class="h-full">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold truncate" title="{{ $item->product->name }}">
                                {{ $item->product->name }}
                            </h3>
                            <p class="text-xs text-gray-500">{{ $item->product->sku }}</p>
                            <p class="text-xs text-gray-400">{{ $item->warehouse->name }}</p>
                            <x-mary-badge value="{{ $this->getStockStatusText($item) }}"
                                class="badge-{{ $this->getStockStatusClass($item) }} badge-sm" />
                        </div>
                    </div>

                    {{-- Stock Information --}}
                    <div class="mb-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">On Hand:</span>
                            <span class="font-semibold">{{ number_format($item->quantity_on_hand) }}</span>
                        </div>
                        @if ($item->quantity_reserved > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Reserved:</span>
                                <span class="text-warning">{{ number_format($item->quantity_reserved) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Available:</span>
                            <span
                                class="font-semibold text-success">{{ number_format($item->quantity_available) }}</span>
                        </div>
                        @if ($item->product->min_stock_level)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Min Level:</span>
                                <span>{{ number_format($item->product->min_stock_level) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Location & Value --}}
                    <div class="mb-4 space-y-1">
                        @if ($item->location)
                            <div class="text-xs text-gray-500">
                                <x-heroicon-o-map-pin class="inline w-3 h-3" /> {{ $item->location }}
                            </div>
                        @endif
                        <div class="text-xs text-gray-500">
                            Value: ₱{{ number_format($item->quantity_on_hand * $item->product->cost_price, 2) }}
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <x-mary-button label="Adjust" wire:click="openAdjustmentModal({{ $item->id }})"
                            class="flex-1 btn-outline btn-xs" />
                        <x-mary-button icon="o-eye" link="#" class="btn-ghost btn-xs" tooltip="View Details" />
                    </div>
                </x-mary-card>
            @empty
                <div class="col-span-full">
                    <x-mary-card>
                        <div class="py-8 text-center">
                            <x-heroicon-o-cube class="w-12 h-12 mx-auto text-gray-400" />
                            <p class="mt-2 text-gray-500">No inventory items found</p>
                        </div>
                    </x-mary-card>
                </div>
            @endforelse
        </div>
    @else
        {{-- Table View --}}
        <x-mary-card>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Warehouse</th>
                            <th>On Hand</th>
                            <th>Reserved</th>
                            <th>Available</th>
                            <th>Min Level</th>
                            <th>Status</th>
                            <th>Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventory as $item)
                            <tr>
                                <td>
                                    <div>
                                        <div class="font-medium">{{ $item->product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->product->sku }}</div>
                                        @if ($item->location)
                                            <div class="text-xs text-gray-400">{{ $item->location }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $item->warehouse->name }}</td>
                                <td class="font-semibold">{{ number_format($item->quantity_on_hand) }}</td>
                                <td class="text-warning">{{ number_format($item->quantity_reserved) }}</td>
                                <td class="font-semibold text-success">{{ number_format($item->quantity_available) }}
                                </td>
                                <td>{{ number_format($item->product->min_stock_level ?? 0) }}</td>
                                <td>
                                    <x-mary-badge value="{{ $this->getStockStatusText($item) }}"
                                        class="badge-{{ $this->getStockStatusClass($item) }} badge-sm" />
                                </td>
                                <td>₱{{ number_format($item->quantity_on_hand * $item->product->cost_price, 2) }}</td>
                                <td>
                                    <div class="flex gap-1">
                                        <x-mary-button icon="o-adjustments-horizontal"
                                            wire:click="openAdjustmentModal({{ $item->id }})"
                                            class="btn-ghost btn-xs" tooltip="Adjust Stock" />
                                        <x-mary-button icon="o-eye" link="#" class="btn-ghost btn-xs"
                                            tooltip="View Details" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="py-8">
                                        <x-heroicon-o-cube class="w-12 h-12 mx-auto text-gray-400" />
                                        <p class="mt-2 text-gray-500">No inventory items found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-mary-card>
    @endif

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $inventory->links() }}
    </div>

    {{-- Stock Adjustment Modal --}}
    <x-mary-modal wire:model="showAdjustmentModal" title="Stock Adjustment"
        subtitle="Adjust inventory for {{ $selectedInventory?->product->name ?? '' }}">

        @if ($selectedInventory)
            <div class="space-y-4">
                {{-- Current Stock Info --}}
                <div class="p-4 rounded-lg bg-base-200">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Current Stock:</span>
                            <span
                                class="ml-2 font-semibold">{{ number_format($selectedInventory->quantity_on_hand) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Warehouse:</span>
                            <span class="ml-2">{{ $selectedInventory->warehouse->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Reserved:</span>
                            <span class="ml-2">{{ number_format($selectedInventory->quantity_reserved) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Available:</span>
                            <span class="ml-2">{{ number_format($selectedInventory->quantity_available) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Adjustment Form --}}
                <div class="grid grid-cols-2 gap-4">
                    <x-mary-select label="Adjustment Type" :options="[
                        ['value' => 'in', 'label' => 'Stock In (+)'],
                        ['value' => 'out', 'label' => 'Stock Out (-)'],
                    ]" wire:model="adjustment_type" />

                    <x-mary-input label="Quantity" wire:model="adjustment_quantity" type="number" min="1"
                        placeholder="Enter quantity" />
                </div>

                <x-mary-input label="Reason" wire:model="adjustment_reason" placeholder="Reason for adjustment" />

                <x-mary-textarea label="Notes (Optional)" wire:model="adjustment_notes"
                    placeholder="Additional notes" rows="2" />
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" wire:click="$set('showAdjustmentModal', false)" />
                <x-mary-button label="Process Adjustment" wire:click="processAdjustment" class="btn-primary" />
            </x-slot:actions>
        @endif
    </x-mary-modal>
</div>
