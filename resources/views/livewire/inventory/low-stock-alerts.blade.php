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
                    Resolve Selected
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
                                @change="$wire.selectedAlerts = $event.target.checked ? @js($alerts->pluck('id')->toArray()) : []">
                        </th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Current Stock</th>
                        <th>Min Level</th>
                        <th>Shortage</th>
                        <th>Severity</th>
                        <th>Alert Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                        <tr class="{{ $alert->current_stock == 0 ? 'bg-error/10' : '' }}">
                            <td>
                                <input type="checkbox" class="checkbox checkbox-sm" value="{{ $alert->id }}"
                                    wire:model="selectedAlerts">
                            </td>
                            <td>
                                <div>
                                    <div class="font-medium">{{ $alert->product->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $alert->product->sku }}</div>
                                    @if ($alert->product->category)
                                        <div class="text-xs text-gray-400">{{ $alert->product->category->name }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $alert->warehouse->name }}</span>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span
                                        class="font-bold text-lg {{ $alert->current_stock == 0 ? 'text-error' : 'text-warning' }}">
                                        {{ number_format($alert->current_stock) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span class="font-medium">{{ number_format($alert->min_stock_level) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span class="font-bold text-error">
                                        {{ number_format($alert->min_stock_level - $alert->current_stock) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <x-mary-badge value="{{ $this->getSeverityText($alert) }}"
                                    class="badge-{{ $this->getSeverityClass($alert) }}" />
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div>{{ $alert->created_at->format('M d, Y') }}</div>
                                    <div class="text-gray-500">{{ $alert->created_at->diffForHumans() }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <x-mary-button icon="o-check" wire:click="resolveAlert({{ $alert->id }})"
                                        class="btn-ghost btn-xs text-success" tooltip="Resolve Alert" />
                                    <x-mary-button icon="o-eye" class="btn-ghost btn-xs" tooltip="View Details" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="py-8">
                                    <x-heroicon-o-check-circle class="w-12 h-12 mx-auto text-green-400" />
                                    <p class="mt-2 text-gray-500">No low stock alerts</p>
                                    <p class="text-sm text-gray-400">All products are adequately stocked!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $alerts->links() }}
        </div>
    </x-mary-card>

    {{-- Create Purchase Order Modal --}}
    <x-mary-modal wire:model="showCreatePOModal" title="Create Purchase Order"
        subtitle="Generate PO for selected low stock items">

        <div class="space-y-4">
            <div class="p-4 rounded-lg bg-info/10">
                <h4 class="font-semibold">Selected Items: {{ count($selectedAlerts) }}</h4>
                <p class="text-sm text-gray-600">A purchase order will be created for the selected low stock items.</p>
            </div>

            <x-mary-select label="Supplier" :options="[]" wire:model="selectedSupplier"
                placeholder="Select supplier" />

            <x-mary-input label="Expected Delivery Date" wire:model="expectedDate" type="date" />

            <div class="p-3 rounded-lg bg-warning/10">
                <p class="text-sm text-warning-700">
                    <x-heroicon-o-information-circle class="inline w-4 h-4 mr-1" />
                    This will create a draft purchase order that you can review and modify before submitting.
                </p>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showCreatePOModal', false)" />
            <x-mary-button label="Create Purchase Order" wire:click="createPurchaseOrder" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>
