<div>
    {{-- Page Header --}}
    <x-mary-header title="Stock Adjustments" subtitle="Manage inventory adjustments and corrections" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search adjustments..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" wire:click="openBulkModal" class="btn-secondary">
                Bulk Adjustment
            </x-mary-button>
            <x-mary-button icon="o-adjustments-horizontal" wire:click="openAdjustmentModal" class="btn-primary">
                New Adjustment
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <x-mary-select placeholder="All Warehouses" :options="$filterOptions['warehouses']" wire:model.live="warehouseFilter" />
        <x-mary-select placeholder="All Types" :options="$filterOptions['types']" wire:model.live="typeFilter" />
        <x-mary-select placeholder="All Dates" :options="$filterOptions['dates']" wire:model.live="dateFilter" />
        <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
            Clear Filters
        </x-mary-button>
    </div>

    {{-- Adjustments Table --}}
    <x-mary-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Adjustment</th>
                        <th>Before/After</th>
                        <th>User</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adjustment)
                        <tr>
                            <td>
                                <div class="text-sm">
                                    <div class="font-medium">{{ $adjustment->created_at->format('M d, Y') }}</div>
                                    <div class="text-gray-500">{{ $adjustment->created_at->format('H:i:s') }}</div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="font-medium">{{ $adjustment->product->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $adjustment->product->sku }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $adjustment->warehouse->name }}</span>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span
                                        class="font-bold text-lg {{ $adjustment->quantity_changed > 0 ? 'text-success' : 'text-error' }}">
                                        {{ $adjustment->quantity_changed > 0 ? '+' : '' }}{{ number_format($adjustment->quantity_changed) }}
                                    </span>
                                    <div class="text-xs text-gray-500">
                                        {{ $adjustment->quantity_changed > 0 ? 'Stock In' : 'Stock Out' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-xs text-center">
                                    <div>{{ number_format($adjustment->quantity_before) }}</div>
                                    <div class="text-gray-400">→</div>
                                    <div class="font-semibold">{{ number_format($adjustment->quantity_after) }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $adjustment->user->name }}</span>
                            </td>
                            <td>
                                @if ($adjustment->notes)
                                    <div class="max-w-xs text-xs" title="{{ $adjustment->notes }}">
                                        {{ Str::limit($adjustment->notes, 50) }}
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="py-8">
                                    <x-heroicon-o-adjustments-horizontal class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-gray-500">No stock adjustments found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $adjustments->links() }}
        </div>
    </x-mary-card>

    {{-- Single Adjustment Modal --}}
    <x-mary-modal wire:model="showAdjustmentModal" title="Stock Adjustment"
        subtitle="Adjust inventory quantity for a product">

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <x-mary-select label="Product" :options="$products->map(fn($p) => ['value' => $p->id, 'label' => $p->name . ' (' . $p->sku . ')'])" wire:model="selectedProduct"
                    placeholder="Select product" />

                <x-mary-select label="Warehouse" :options="$warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name])" wire:model="selectedWarehouse"
                    placeholder="Select warehouse" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-mary-select label="Adjustment Type" :options="[
                    ['value' => 'in', 'label' => 'Stock In (+)'],
                    ['value' => 'out', 'label' => 'Stock Out (-)'],
                ]" wire:model="adjustmentType" />

                <x-mary-input label="Quantity" wire:model="quantity" type="number" min="1"
                    placeholder="Enter quantity" />
            </div>

            <x-mary-input label="Reason" wire:model="reason" placeholder="Reason for adjustment (required)" />

            <x-mary-textarea label="Additional Notes" wire:model="notes" placeholder="Optional additional details"
                rows="3" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showAdjustmentModal', false)" />
            <x-mary-button label="Process Adjustment" wire:click="processAdjustment" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Bulk Adjustments Modal --}}
    <x-mary-modal wire:model="showBulkModal" title="Bulk Stock Adjustments"
        subtitle="Process multiple adjustments at once">

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-semibold">Adjustment Items</h4>
                <x-mary-button icon="o-plus" wire:click="addBulkAdjustment" class="btn-sm btn-primary">
                    Add Item
                </x-mary-button>
            </div>

            @if (count($bulkAdjustments) > 0)
                <div class="space-y-3 overflow-y-auto max-h-96">
                    @foreach ($bulkAdjustments as $index => $adjustment)
                        <div class="p-4 border rounded-lg bg-base-200">
                            <div class="grid items-end grid-cols-6 gap-3">
                                <x-mary-select label="Product" :options="$products->map(fn($p) => ['value' => $p->id, 'label' => $p->name])"
                                    wire:model="bulkAdjustments.{{ $index }}.product_id" placeholder="Select" />

                                <x-mary-select label="Warehouse" :options="$warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name])"
                                    wire:model="bulkAdjustments.{{ $index }}.warehouse_id"
                                    placeholder="Select" />

                                <x-mary-select label="Type" :options="[
                                    ['value' => 'in', 'label' => 'In (+)'],
                                    ['value' => 'out', 'label' => 'Out (-)'],
                                ]"
                                    wire:model="bulkAdjustments.{{ $index }}.type" />

                                <x-mary-input label="Quantity"
                                    wire:model="bulkAdjustments.{{ $index }}.quantity" type="number"
                                    min="1" />

                                <x-mary-input label="Reason" wire:model="bulkAdjustments.{{ $index }}.reason"
                                    placeholder="Required" />

                                <x-mary-button icon="o-trash" wire:click="removeBulkAdjustment({{ $index }})"
                                    class="btn-ghost btn-sm text-error" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center border-2 border-gray-300 border-dashed rounded-lg">
                    <x-heroicon-o-plus class="w-12 h-12 mx-auto text-gray-400" />
                    <p class="mt-2 text-gray-500">No adjustments added</p>
                    <x-mary-button label="Add First Adjustment" wire:click="addBulkAdjustment"
                        class="mt-3 btn-primary" />
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showBulkModal', false)" />
            <x-mary-button label="Process All Adjustments" wire:click="processBulkAdjustments" class="btn-primary"
                :disabled="count($bulkAdjustments) === 0" />
        </x-slot:actions>
    </x-mary-modal>
</div>
