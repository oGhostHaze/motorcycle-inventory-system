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
        <x-mary-select placeholder="All Warehouses" :options="$filterOptions['warehouses']" wire:model.live="warehouseFilter"
            option-value="value" option-label="label" />
        <x-mary-select placeholder="All Categories" :options="$filterOptions['categories']" wire:model.live="categoryFilter"
            option-value="value" option-label="label" />
        <x-mary-select placeholder="All Stock Levels" :options="$filterOptions['stock']" wire:model.live="stockFilter"
            option-value="value" option-label="label" />
        <x-mary-select placeholder="Product Status" :options="$filterOptions['status']" wire:model.live="statusFilter"
            option-value="value" option-label="label" />
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
                        <x-mary-button icon="o-eye" wire:click="viewDetails({{ $item->id }})"
                            class="btn-ghost btn-xs" tooltip="View Details" />
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
            <div class="min-h-screen overflow-x-auto">
                <table class="table h-full table-zebra">
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
                                        <x-mary-button icon="o-eye" wire:click="viewDetails({{ $item->id }})"
                                            class="btn-ghost btn-xs" tooltip="View Details" />
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
                    ]" wire:model="adjustment_type"
                        option-value="value" option-label="label" />

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

    {{-- Product Details Modal --}}
    <x-mary-modal wire:model="showDetailsModal" title="Product Details" subtitle="{{ $selectedProduct?->name }}"
        box-class="w-11/12 max-w-4xl">
        @if ($selectedProduct && $selectedInventory)
            <div class="space-y-6">
                {{-- Basic Information --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold">Basic Information</h4>
                        <div class="space-y-2">
                            <div><strong>Name:</strong> {{ $selectedProduct->name }}</div>
                            <div><strong>SKU:</strong> {{ $selectedProduct->sku }}</div>
                            @if ($selectedProduct->barcode)
                                <div><strong>Barcode:</strong> {{ $selectedProduct->barcode }}</div>
                            @endif
                            <div><strong>Category:</strong> {{ $selectedProduct->category?->name ?? 'No category' }}
                            </div>
                            @if ($selectedProduct->subcategory)
                                <div><strong>Subcategory:</strong> {{ $selectedProduct->subcategory->name }}</div>
                            @endif
                            @if ($selectedProduct->brand)
                                <div><strong>Brand:</strong> {{ $selectedProduct->brand->name }}</div>
                            @endif
                            @if ($selectedProduct->part_number)
                                <div><strong>Part Number:</strong> {{ $selectedProduct->part_number }}</div>
                            @endif
                            @if ($selectedProduct->oem_number)
                                <div><strong>OEM Number:</strong> {{ $selectedProduct->oem_number }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold">Pricing & Stock</h4>
                        <div class="space-y-2">
                            <div><strong>Cost Price:</strong> ₱{{ number_format($selectedProduct->cost_price, 2) }}
                            </div>
                            <div><strong>Selling Price:</strong>
                                ₱{{ number_format($selectedProduct->selling_price, 2) }}</div>
                            @if ($selectedProduct->wholesale_price)
                                <div><strong>Wholesale Price:</strong>
                                    ₱{{ number_format($selectedProduct->wholesale_price, 2) }}</div>
                            @endif
                            <div><strong>Min Stock Level:</strong>
                                {{ number_format($selectedProduct->min_stock_level ?? 0) }}</div>
                            @if ($selectedProduct->max_stock_level)
                                <div><strong>Max Stock Level:</strong>
                                    {{ number_format($selectedProduct->max_stock_level) }}</div>
                            @endif
                            @if ($selectedProduct->reorder_point)
                                <div><strong>Reorder Point:</strong>
                                    {{ number_format($selectedProduct->reorder_point) }}</div>
                            @endif
                            @if ($selectedProduct->reorder_quantity)
                                <div><strong>Reorder Quantity:</strong>
                                    {{ number_format($selectedProduct->reorder_quantity) }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Current Warehouse Stock Info --}}
                <div>
                    <h4 class="mb-3 text-lg font-semibold">Current Stock ({{ $selectedInventory->warehouse->name }})
                    </h4>
                    <div class="grid grid-cols-2 gap-4 p-4 rounded-lg md:grid-cols-4 bg-base-200">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-info">
                                {{ number_format($selectedInventory->quantity_on_hand) }}</div>
                            <div class="text-sm text-gray-600">On Hand</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-warning">
                                {{ number_format($selectedInventory->quantity_reserved) }}</div>
                            <div class="text-sm text-gray-600">Reserved</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-success">
                                {{ number_format($selectedInventory->quantity_available) }}</div>
                            <div class="text-sm text-gray-600">Available</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold">
                                ₱{{ number_format($selectedInventory->quantity_on_hand * $selectedProduct->cost_price, 2) }}
                            </div>
                            <div class="text-sm text-gray-600">Total Value</div>
                        </div>
                    </div>
                    @if ($selectedInventory->location)
                        <div class="mt-2 text-sm text-gray-600">
                            <x-heroicon-o-map-pin class="inline w-4 h-4" /> Location:
                            {{ $selectedInventory->location }}
                        </div>
                    @endif
                </div>

                {{-- All Warehouse Stock Levels --}}
                @if ($selectedProduct->inventory->count() > 1)
                    <div>
                        <h4 class="mb-3 text-lg font-semibold">Stock by All Warehouses</h4>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Warehouse</th>
                                        <th>On Hand</th>
                                        <th>Reserved</th>
                                        <th>Available</th>
                                        <th>Location</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedProduct->inventory as $inventory)
                                        <tr
                                            class="{{ $inventory->id === $selectedInventory->id ? 'bg-base-200' : '' }}">
                                            <td>
                                                {{ $inventory->warehouse->name }}
                                                @if ($inventory->id === $selectedInventory->id)
                                                    <x-mary-badge value="Current" class="badge-info badge-xs" />
                                                @endif
                                            </td>
                                            <td class="font-semibold">
                                                {{ number_format($inventory->quantity_on_hand) }}</td>
                                            <td class="text-warning">
                                                {{ number_format($inventory->quantity_reserved) }}</td>
                                            <td class="font-semibold text-success">
                                                {{ number_format($inventory->quantity_available) }}</td>
                                            <td>{{ $inventory->location ?? '-' }}</td>
                                            <td>₱{{ number_format($inventory->quantity_on_hand * $selectedProduct->cost_price, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Recent Stock Movements --}}
                @if ($selectedProduct->stockMovements->count() > 0)
                    <div>
                        <h4 class="mb-3 text-lg font-semibold">Recent Stock Movements</h4>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>Previous</th>
                                        <th>New</th>
                                        <th>Reason</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedProduct->stockMovements as $movement)
                                        <tr>
                                            <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <x-mary-badge
                                                    value="{{ ucwords(str_replace('_', ' ', $movement->type)) }}"
                                                    class="badge-{{ in_array($movement->type, ['adjustment', 'purchase']) && $movement->quantity_changed > 0 ? 'success' : 'warning' }} badge-xs" />
                                            </td>
                                            <td
                                                class="font-semibold {{ $movement->quantity_changed > 0 ? 'text-success' : 'text-warning' }}">
                                                {{ $movement->quantity_changed > 0 ? '+' : '' }}{{ number_format($movement->quantity_changed) }}
                                            </td>
                                            <td>{{ number_format($movement->quantity_before) }}</td>
                                            <td>{{ number_format($movement->quantity_after) }}</td>
                                            <td>{{ $movement->notes ?? '-' }}</td>
                                            <td>{{ $movement->user->name ?? 'System' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Product Specifications --}}
                @if (
                    $selectedProduct->description ||
                        $selectedProduct->weight ||
                        $selectedProduct->color ||
                        $selectedProduct->size ||
                        $selectedProduct->material)
                    <div>
                        <h4 class="mb-3 text-lg font-semibold">Product Specifications</h4>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @if ($selectedProduct->description)
                                <div>
                                    <strong>Description:</strong>
                                    <p class="mt-1 text-sm text-gray-600">{{ $selectedProduct->description }}</p>
                                </div>
                            @endif

                            <div class="space-y-2">
                                @if ($selectedProduct->weight)
                                    <div><strong>Weight:</strong> {{ $selectedProduct->weight }} kg</div>
                                @endif
                                @if ($selectedProduct->color)
                                    <div><strong>Color:</strong> {{ $selectedProduct->color }}</div>
                                @endif
                                @if ($selectedProduct->size)
                                    <div><strong>Size:</strong> {{ $selectedProduct->size }}</div>
                                @endif
                                @if ($selectedProduct->material)
                                    <div><strong>Material:</strong> {{ $selectedProduct->material }}</div>
                                @endif
                                @if ($selectedProduct->warranty_months)
                                    <div><strong>Warranty:</strong> {{ $selectedProduct->warranty_months }} months
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Status & Tracking --}}
                <div>
                    <h4 class="mb-3 text-lg font-semibold">Status & Settings</h4>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <strong>Status:</strong>
                            <x-mary-badge value="{{ ucfirst($selectedProduct->status) }}"
                                class="badge-{{ $selectedProduct->status === 'active' ? 'success' : ($selectedProduct->status === 'inactive' ? 'warning' : 'error') }} badge-sm" />
                        </div>
                        <div>
                            <strong>Serial Tracking:</strong>
                            <x-mary-badge value="{{ $selectedProduct->track_serial ? 'Yes' : 'No' }}"
                                class="badge-{{ $selectedProduct->track_serial ? 'success' : 'ghost' }} badge-sm" />
                        </div>
                        <div>
                            <strong>Warranty Tracking:</strong>
                            <x-mary-badge value="{{ $selectedProduct->track_warranty ? 'Yes' : 'No' }}"
                                class="badge-{{ $selectedProduct->track_warranty ? 'success' : 'ghost' }} badge-sm" />
                        </div>
                        <div>
                            <strong>Created:</strong>
                            <span class="text-sm">{{ $selectedProduct->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Internal Notes --}}
                @if ($selectedProduct->internal_notes)
                    <div>
                        <h4 class="mb-3 text-lg font-semibold">Internal Notes</h4>
                        <div class="p-3 rounded-lg bg-base-200">
                            <p class="text-sm">{{ $selectedProduct->internal_notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-mary-button label="Close" wire:click="$set('showDetailsModal', false)" />
                <x-mary-button label="Adjust Stock" wire:click="openAdjustmentFromDetails" class="btn-primary" />
            </x-slot:actions>
        @endif
    </x-mary-modal>

    {{-- JavaScript for handling modal transitions --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-adjustment-modal', () => {
                // Small delay to ensure the details modal is fully closed
                setTimeout(() => {
                    @this.set('showAdjustmentModal', true);
                }, 150);
            });
        });
    </script>
</div>
