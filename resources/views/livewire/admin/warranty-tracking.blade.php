<div>
    <x-mary-header title="Warranty Tracking" separator>
        <x-slot:actions>
            <x-mary-button label="Refresh" wire:click="$refresh" icon="o-arrow-path" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <x-mary-stat title="Active Warranties" value="{{ number_format($stats['active_warranties']) }}"
            icon="o-shield-check" class="bg-success text-success-content" />
        <x-mary-stat title="Expiring Soon" value="{{ number_format($stats['expiring_soon']) }}"
            icon="o-exclamation-triangle" class="bg-warning text-warning-content" />
        <x-mary-stat title="Expired This Month" value="{{ number_format($stats['expired_this_month']) }}" icon="o-clock"
            class="bg-error text-error-content" />
        <x-mary-stat title="Claims This Month" value="{{ number_format($stats['claims_this_month']) }}"
            icon="o-document-text" class="bg-info text-info-content" />
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <x-mary-input label="Search" wire:model.live="search" placeholder="Serial, product, or customer..." />
        <x-mary-select label="Status" wire:model.live="statusFilter" :options="[
            ['id' => 'all', 'name' => 'All Warranties'],
            ['id' => 'active', 'name' => 'Active'],
            ['id' => 'expiring', 'name' => 'Expiring Soon'],
            ['id' => 'expired', 'name' => 'Expired'],
        ]" />
    </div>

    {{-- Warranties Table --}}
    <x-mary-table :headers="[
        ['key' => 'serial_number', 'label' => 'Serial Number'],
        ['key' => 'product.name', 'label' => 'Product'],
        ['key' => 'customer.name', 'label' => 'Customer'],
        ['key' => 'sold_at', 'label' => 'Purchase Date'],
        ['key' => 'warranty_expires_at', 'label' => 'Warranty Expires'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'actions', 'label' => 'Actions'],
    ]" :rows="$warranties" with-pagination>

        @scope('cell_warranty_expires_at', $warranty)
            <div class="flex items-center gap-2">
                @if ($warranty->warranty_expires_at > now())
                    @if ($warranty->warranty_expires_at <= now()->addDays(30))
                        <x-mary-badge value="Expiring Soon" class="badge-warning badge-sm" />
                    @else
                        <x-mary-badge value="Active" class="badge-success badge-sm" />
                    @endif
                @else
                    <x-mary-badge value="Expired" class="badge-error badge-sm" />
                @endif
                <span class="text-sm">{{ $warranty->warranty_expires_at->format('M d, Y') }}</span>
            </div>
        @endscope

        @scope('cell_sold_at', $warranty)
            {{ $warranty->sold_at?->format('M d, Y') ?? '-' }}
        @endscope

        @scope('cell_actions', $warranty)
            <div class="flex gap-2">
                @if ($warranty->warranty_expires_at > now())
                    <x-mary-button label="Claim" wire:click="openClaimModal({{ $warranty->id }})"
                        class="btn-sm btn-outline" />
                @endif
            </div>
        @endscope
    </x-mary-table>

    {{-- Warranty Claim Modal --}}
    <x-mary-modal wire:model="showClaimModal" title="Create Warranty Claim">
        <div class="space-y-4">
            <x-mary-textarea label="Issue Description" wire:model="issueDescription"
                placeholder="Describe the issue with the product..." rows="4" />

            <x-mary-input label="Claim Amount" wire:model="claimAmount" type="number" step="0.01" />

            <x-mary-select label="Resolution Type" wire:model="resolutionType" :options="[
                ['id' => 'repair', 'name' => 'Repair'],
                ['id' => 'replace', 'name' => 'Replace'],
                ['id' => 'refund', 'name' => 'Refund'],
                ['id' => 'denied', 'name' => 'Denied'],
            ]" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showClaimModal', false)" />
            <x-mary-button label="Submit Claim" wire:click="submitClaim" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>
