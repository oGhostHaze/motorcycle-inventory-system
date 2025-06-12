<div>
    {{-- Page Header --}}
    <x-mary-header title="Low Stock Alerts" subtitle="Monitor and manage low inventory alerts" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search products..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-arrow-path" wire:click="refreshAlerts" class="btn-ghost" tooltip="Refresh Alerts" />
            @if (count($selectedAlerts) > 0)
                <x-mary-button icon="o-check" wire:click="resolveMultiple" class="btn-success">
                    Acknowledge Selected
                </x-mary-button>
                <x-mary-button icon="o-document-plus" wire:click="openCreatePOModal" class="btn-primary">
                    Create PO
                </x-mary-button>
            @endif
        </x-slot:actions>
    </x-mary-header>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <x-mary-stat title="Total Alerts" description="Active low stock alerts" value="{{ $totalAlerts }}"
            icon="o-exclamation-triangle" color="text-warning" />

        <x-mary-stat title="Critical Items" description="Out of stock items" value="{{ $criticalAlerts }}"
            icon="o-x-circle" color="text-error" />

        <x-mary-stat title="Estimated Value" description="To reach min levels"
            value="₱{{ number_format($totalValue, 2) }}" icon="o-banknotes" color="text-info" />
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <x-mary-select placeholder="All Warehouses" :options="$filterOptions['warehouses']" wire:model.live="warehouseFilter"
            option-value="value" option-label="label" />
        <x-mary-select placeholder="All Status" :options="$filterOptions['statuses']" wire:model.live="statusFilter" option-value="value"
            option-label="label" />
        <x-mary-select placeholder="All Severity" :options="$filterOptions['severities']" wire:model.live="severityFilter"
            option-value="value" option-label="label" />
        <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
            Clear Filters
        </x-mary-button>
    </div>

    {{-- Alerts Table --}}
    <x-mary-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="checkbox checkbox-sm"
                                @change="$wire.selectedAlerts = $event.target.checked ?
                                @js($alerts->pluck('id')->toArray()) : []">
                        </th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Current Stock</th>
                        <th>Min Level</th>
                        <th>Shortage</th>
                        <th>Severity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                        <tr class="{{ $alert->quantity_on_hand == 0 ? 'bg-error/10' : '' }}">
                            <td>
                                <input type="checkbox" class="checkbox checkbox-sm" value="{{ $alert->id }}"
                                    wire:model="selectedAlerts">
                            </td>
                            <td>
                                <div>
                                    <div class="font-medium">{{ $alert->product_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $alert->sku }}</div>
                                    @if ($alert->category_name)
                                        <div class="text-xs text-gray-400">{{ $alert->category_name }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $alert->warehouse_name }}</span>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span
                                        class="font-bold text-lg {{ $alert->quantity_on_hand == 0 ? 'text-error' : ($alert->quantity_on_hand <= $alert->min_stock_level * 0.5 ? 'text-warning' : 'text-info') }}">
                                        {{ number_format($alert->quantity_on_hand) }}
                                    </span>
                                    <div class="text-xs text-gray-500">units</div>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span class="font-medium">{{ number_format($alert->min_stock_level) }}</span>
                                    <div class="text-xs text-gray-500">minimum</div>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    @php
                                        $shortage = max(0, $alert->min_stock_level - $alert->quantity_on_hand);
                                    @endphp
                                    <span class="font-medium text-error">
                                        {{ number_format($shortage) }}
                                    </span>
                                    <div class="text-xs text-gray-500">needed</div>
                                </div>
                            </td>
                            <td>
                                <x-mary-badge value="{{ $this->getSeverityText($alert) }}"
                                    class="badge-{{ $this->getSeverityClass($alert) }}" />
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <x-mary-button icon="o-check" wire:click="resolveAlert({{ $alert->id }})"
                                        class="btn-ghost btn-sm" tooltip="Acknowledge Alert" />
                                    <x-mary-button icon="o-eye"
                                        onclick="window.open('/inventory/stock-levels?search={{ $alert->sku }}', '_blank')"
                                        class="btn-ghost btn-sm" tooltip="View Details" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <x-mary-icon name="o-check-circle" class="w-16 h-16 text-success" />
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">No low stock alerts</h3>
                                        <p class="text-sm text-gray-500">All products are adequately stocked!</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($alerts->hasPages())
            <div class="mt-4">
                {{ $alerts->links() }}
            </div>
        @endif
    </x-mary-card>

    {{-- Create Purchase Order Modal --}}
    <x-mary-modal wire:model="showCreatePOModal" title="Create Purchase Order"
        subtitle="Generate PO for selected items">
        <div class="space-y-4">
            <x-mary-select label="Supplier" wire:model="selectedSupplier" :options="[]"
                placeholder="Select supplier..." required />

            <x-mary-input label="Expected Delivery Date" wire:model="expectedDate" type="date" required />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showCreatePOModal', false)" />
            <x-mary-button label="Create Purchase Order" wire:click="createPurchaseOrder" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>
