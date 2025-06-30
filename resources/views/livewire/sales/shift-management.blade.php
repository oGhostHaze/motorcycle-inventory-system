<div>
    {{-- Page Header --}}
    <x-mary-header title="Shift Management" subtitle="Manage sales shifts and cash accountability" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-input placeholder="Search shifts..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            @if ($currentShift)
                <x-mary-badge value="Active Shift: {{ $currentShift->shift_number }}" class="badge-success" />
                <x-mary-button icon="o-stop" wire:click="openEndShiftModal" class="btn-error">
                    End Shift
                </x-mary-button>
            @else
                <x-mary-button icon="o-play" wire:click="openStartShiftModal" class="btn-primary">
                    Start Shift
                </x-mary-button>
            @endif
        </x-slot:actions>
    </x-mary-header>

    {{-- Current Shift Info --}}
    @if ($currentShift)
        <div class="mb-6">
            <x-mary-card class="bg-gradient-to-r from-primary/10 to-success/10 border-primary/20">
                {{-- UPDATED: Add returns to the existing grid --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary">{{ $currentShift->shift_number }}</div>
                        <div class="text-sm text-gray-600">Current Shift</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-success">
                            ₱{{ number_format($currentShift->opening_cash, 2) }}</div>
                        <div class="text-sm text-gray-600">Opening Cash</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-info">{{ $currentShift->total_transactions }}</div>
                        <div class="text-sm text-gray-600">Transactions</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-warning">₱{{ number_format($currentShift->total_sales, 2) }}
                        </div>
                        <div class="text-sm text-gray-600">Total Sales</div>
                    </div>
                    {{-- NEW: Add returns column --}}
                    <div class="text-center">
                        <div class="text-2xl font-bold text-error">{{ $currentShift->total_returns_count ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Returns</div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <div class="text-sm text-gray-600">
                        Started: {{ $currentShift->started_at->format('M d, Y H:i') }}
                        ({{ $this->formatShiftDuration($currentShift) }})
                    </div>
                </div>
            </x-mary-card>
        </div>
    @endif

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <x-mary-select placeholder="All Warehouses" :options="$filterOptions['warehouses']" wire:model.live="warehouseFilter"
            option-value="value" option-label="label" />
        <x-mary-select placeholder="All Status" :options="$filterOptions['statuses']" wire:model.live="statusFilter" option-value="value"
            option-label="label" />
        <x-mary-select placeholder="All Dates" :options="$filterOptions['dates']" wire:model.live="dateFilter" option-value="value"
            option-label="label" />
        <x-mary-button icon="o-x-mark" wire:click="clearFilters" class="btn-ghost">
            Clear Filters
        </x-mary-button>
    </div>

    {{-- Shifts Table --}}
    <x-mary-card>
        <div class="min-h-screen overflow-x-auto">
            <table class="table h-full table-zebra">
                <thead>
                    <tr>
                        <th>Shift #</th>
                        <th>Cashier</th>
                        <th>Warehouse</th>
                        <th>Duration</th>
                        <th>Cash Flow</th>
                        <th>Sales</th>
                        <th>Returns</th>
                        <th>Difference</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        <tr>
                            <td>
                                <div class="font-bold">{{ $shift->shift_number }}</div>
                                <div class="text-sm text-gray-500">{{ $shift->started_at->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <div class="font-medium">{{ $shift->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $shift->user->role_display_name }}</div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $shift->warehouse->name }}</span>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div>{{ $shift->started_at->format('H:i') }}</div>
                                    @if ($shift->ended_at)
                                        <div class="text-gray-500">{{ $shift->ended_at->format('H:i') }}</div>
                                        <div class="flex items-center gap-1 text-xs text-info">
                                            <x-heroicon-o-clock class="w-3 h-3" />
                                            {{ $this->formatShiftDuration($shift) }}
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1 text-success">
                                            <x-heroicon-o-signal class="w-3 h-3" />
                                            Active
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div>Open: ₱{{ number_format($shift->opening_cash, 2) }}</div>
                                    @if ($shift->closing_cash !== null)
                                        <div>Close: ₱{{ number_format($shift->closing_cash, 2) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <div class="font-semibold">₱{{ number_format($shift->total_sales, 2) }}</div>
                                    <div class="text-xs text-gray-500">{{ $shift->total_transactions }} txns</div>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <div class="font-semibold text-error">{{ $shift->total_returns_count ?? 0 }}</div>
                                    <div class="text-xs text-gray-500">
                                        @if (($shift->total_returns_amount ?? 0) > 0)
                                            ₱{{ number_format($shift->total_returns_amount, 2) }}
                                        @else
                                            No returns
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($shift->cash_difference !== null)
                                    <div class="flex items-center gap-1">
                                        @if ($shift->cash_difference > 0)
                                            <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-warning" />
                                        @elseif ($shift->cash_difference < 0)
                                            <x-heroicon-o-arrow-trending-down class="w-4 h-4 text-error" />
                                        @else
                                            <x-heroicon-o-check-circle class="w-4 h-4 text-success" />
                                        @endif
                                        <x-mary-badge
                                            value="{{ $this->getCashDifferenceText($shift->cash_difference) }}"
                                            class="badge-{{ $this->getCashDifferenceClass($shift->cash_difference) }} badge-sm whitespace-nowrap" />
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td>
                                <x-mary-badge value="{{ ucfirst($shift->status) }}"
                                    class="badge-{{ $this->getShiftStatusClass($shift->status) }}" />
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                                        <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                                    </div>
                                    <ul tabindex="0"
                                        class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                                        <li><a wire:click="viewShiftDetails({{ $shift->id }})">
                                                <x-heroicon-o-eye class="w-4 h-4" /> View Details</a></li>
                                        <li><a wire:click="printShiftReport({{ $shift->id }})">
                                                <x-heroicon-o-printer class="w-4 h-4" /> Print Report</a></li>
                                        @if ($shift->status === 'active' && $shift->total_transactions === 0)
                                            <li><a wire:click="cancelShift({{ $shift->id }})"
                                                    wire:confirm="Are you sure you want to cancel this shift?"
                                                    class="text-error">
                                                    <x-heroicon-o-x-mark class="w-4 h-4" /> Cancel Shift</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="py-8">
                                    <x-heroicon-o-clock class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-gray-500">No shifts found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $shifts->links() }}
        </div>
    </x-mary-card>

    {{-- Start Shift Modal --}}
    <x-mary-modal wire:model="showStartShiftModal" title="Start New Shift" subtitle="Begin your sales shift">
        <div class="space-y-4">
            <x-mary-select label="Warehouse" :options="$warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name])" wire:model="selectedWarehouse"
                placeholder="Select warehouse" option-value="value" option-label="label" />

            <x-mary-input label="Opening Cash Amount" wire:model="openingCash" type="number" step="0.01"
                placeholder="0.00" hint="Enter the cash amount in your drawer" />

            <x-mary-textarea label="Opening Notes (Optional)" wire:model="openingNotes"
                placeholder="Any notes about the opening..." rows="3" />

            <div class="p-4 rounded-lg bg-info/10">
                <div class="flex items-start space-x-2">
                    <x-heroicon-o-information-circle class="w-5 h-5 mt-0.5 text-info" />
                    <div class="text-sm">
                        <p class="font-medium text-info">Important:</p>
                        <ul class="mt-1 space-y-1 text-gray-700">
                            <li>Count your cash drawer carefully before starting</li>
                            <li>This amount will be used to calculate your end-of-shift balance</li>
                            <li>You cannot process sales without an active shift</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showStartShiftModal', false)" />
            <x-mary-button label="Start Shift" wire:click="startShift" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- End Shift Modal --}}
    <x-mary-modal wire:model="showEndShiftModal" title="End Current Shift"
        subtitle="Shift: {{ $currentShift?->shift_number }}">

        @if ($currentShift)
            <div class="space-y-4">
                {{-- Shift Summary --}}
                <div class="p-4 rounded-lg bg-base-200">
                    <h4 class="mb-3 font-semibold">Shift Summary</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Opening Cash:</span>
                            <span
                                class="ml-2 font-semibold">₱{{ number_format($currentShift->opening_cash, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Cash Sales:</span>
                            <span class="ml-2 font-semibold">₱{{ number_format($currentShift->cash_sales, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Card Sales:</span>
                            <span class="ml-2 font-semibold">₱{{ number_format($currentShift->card_sales, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Total Transactions:</span>
                            <span class="ml-2 font-semibold">{{ $currentShift->total_transactions }}</span>
                        </div>
                        {{-- NEW: Add returns summary --}}
                        <div>
                            <span class="text-gray-600">Returns:</span>
                            <span
                                class="ml-2 font-semibold text-error">{{ $currentShift->total_returns_count ?? 0 }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Return Amount:</span>
                            <span
                                class="ml-2 font-semibold text-error">₱{{ number_format($currentShift->total_returns_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="col-span-2 pt-2 border-t">
                            <span class="text-gray-600">Expected Cash in Drawer:</span>
                            <span
                                class="ml-2 text-lg font-bold text-success">₱{{ number_format($currentShift->expected_cash, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Closing Cash Input --}}
                <x-mary-input label="Actual Cash in Drawer" wire:model.live="closingCash" type="number"
                    step="0.01" hint="Count all cash in your drawer and enter the total amount" />

                {{-- Cash Difference Display --}}
                @if ($closingCash && is_numeric($closingCash))
                    @php
                        $difference = $closingCash - $currentShift->expected_cash;
                    @endphp
                    <div
                        class="p-3 rounded-lg {{ $difference == 0 ? 'bg-success/10 border-success/20' : ($difference > 0 ? 'bg-warning/10 border-warning/20' : 'bg-error/10 border-error/20') }} border">
                        <div class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if ($difference > 0)
                                    <x-heroicon-o-arrow-trending-up class="w-6 h-6 text-warning" />
                                    <div class="text-lg font-bold text-warning">
                                        Cash Over: +₱{{ number_format($difference, 2) }}
                                    </div>
                                @elseif ($difference < 0)
                                    <x-heroicon-o-arrow-trending-down class="w-6 h-6 text-error" />
                                    <div class="text-lg font-bold text-error">
                                        Cash Short: -₱{{ number_format(abs($difference), 2) }}
                                    </div>
                                @else
                                    <x-heroicon-o-check-circle class="w-6 h-6 text-success" />
                                    <div class="text-lg font-bold text-success">
                                        Perfect Balance! ✓
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <x-mary-textarea label="Closing Notes (Optional)" wire:model="closingNotes"
                    placeholder="Any notes about the shift end..." rows="3" />
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="$set('showEndShiftModal', false)" />
            <x-mary-button label="End Shift" wire:click="endShift" class="btn-error" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Shift Details Modal --}}
    <x-mary-modal wire:model="showShiftDetailsModal" title="Shift Details"
        subtitle="{{ $selectedShift?->shift_number }}" box-class="w-11/12 max-w-4xl">

        @if ($selectedShift)
            <div class="space-y-6">
                {{-- Basic Info --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-3">
                        <h4 class="font-semibold">Shift Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Shift Number:</strong> {{ $selectedShift->shift_number }}</div>
                            <div><strong>Cashier:</strong> {{ $selectedShift->user->name }}</div>
                            <div><strong>Warehouse:</strong> {{ $selectedShift->warehouse->name }}</div>
                            <div><strong>Started:</strong> {{ $selectedShift->started_at->format('M d, Y H:i') }}</div>
                            @if ($selectedShift->ended_at)
                                <div><strong>Ended:</strong> {{ $selectedShift->ended_at->format('M d, Y H:i') }}</div>
                                <div class="flex items-center gap-1">
                                    <strong>Duration:</strong>
                                    <x-heroicon-o-clock class="w-4 h-4 text-info" />
                                    <span>{{ $this->formatShiftDuration($selectedShift) }}</span>
                                </div>
                            @endif
                            <div><strong>Status:</strong>
                                <x-mary-badge value="{{ ucfirst($selectedShift->status) }}"
                                    class="badge-{{ $this->getShiftStatusClass($selectedShift->status) }} badge-sm" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h4 class="font-semibold">Financial Summary</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Opening Cash:</strong> ₱{{ number_format($selectedShift->opening_cash, 2) }}
                            </div>
                            @if ($selectedShift->closing_cash !== null)
                                <div><strong>Closing Cash:</strong>
                                    ₱{{ number_format($selectedShift->closing_cash, 2) }}</div>
                                <div><strong>Expected Cash:</strong>
                                    ₱{{ number_format($selectedShift->expected_cash, 2) }}</div>
                                <div class="flex items-center gap-1">
                                    <strong>Cash Difference:</strong>
                                    @if ($selectedShift->cash_difference > 0)
                                        <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-warning" />
                                    @elseif ($selectedShift->cash_difference < 0)
                                        <x-heroicon-o-arrow-trending-down class="w-4 h-4 text-error" />
                                    @else
                                        <x-heroicon-o-check-circle class="w-4 h-4 text-success" />
                                    @endif
                                    <x-mary-badge
                                        value="{{ $this->getCashDifferenceText($selectedShift->cash_difference) }}"
                                        class="badge-{{ $this->getCashDifferenceClass($selectedShift->cash_difference) }} badge-sm" />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sales Breakdown --}}
                <div>
                    <h4 class="mb-3 font-semibold">Sales Breakdown</h4>
                    {{-- UPDATED: Change from 4 columns to 5 --}}
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                        <div class="p-4 text-center rounded-lg bg-primary/10">
                            <div class="text-2xl font-bold text-primary">
                                ₱{{ number_format($selectedShift->total_sales, 2) }}</div>
                            <div class="text-sm text-gray-600">Total Sales</div>
                        </div>
                        <div class="p-4 text-center rounded-lg bg-success/10">
                            <div class="text-2xl font-bold text-success">
                                ₱{{ number_format($selectedShift->cash_sales, 2) }}</div>
                            <div class="text-sm text-gray-600">Cash Sales</div>
                        </div>
                        <div class="p-4 text-center rounded-lg bg-info/10">
                            <div class="text-2xl font-bold text-info">
                                ₱{{ number_format($selectedShift->card_sales, 2) }}</div>
                            <div class="text-sm text-gray-600">Card Sales</div>
                        </div>
                        <div class="p-4 text-center rounded-lg bg-warning/10">
                            <div class="text-2xl font-bold text-warning">{{ $selectedShift->total_transactions }}
                            </div>
                            <div class="text-sm text-gray-600">Transactions</div>
                        </div>
                        {{-- NEW: Add returns breakdown --}}
                        <div class="p-4 text-center rounded-lg bg-error/10">
                            <div class="text-2xl font-bold text-error">{{ $selectedShift->total_returns_count ?? 0 }}
                            </div>
                            <div class="text-sm text-gray-600">Returns</div>
                        </div>
                    </div>
                </div>

                @if (($selectedShift->total_returns_count ?? 0) > 0)
                    <div>
                        <h4 class="mb-3 font-semibold">Return Summary</h4>
                        <div class="p-4 rounded-lg bg-error/10">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong>Total Returns:</strong> {{ $selectedShift->total_returns_count ?? 0 }}
                                </div>
                                <div>
                                    <strong>Return Amount:</strong>
                                    ₱{{ number_format($selectedShift->total_returns_amount ?? 0, 2) }}
                                </div>
                                <div>
                                    <strong>Processed Returns:</strong>
                                    {{ $selectedShift->processed_returns_count ?? 0 }}
                                </div>
                                <div>
                                    <strong>Pending Returns:</strong>
                                    {{ ($selectedShift->total_returns_count ?? 0) - ($selectedShift->processed_returns_count ?? 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- Notes --}}
                @if ($selectedShift->opening_notes || $selectedShift->closing_notes)
                    <div>
                        <h4 class="mb-3 font-semibold">Notes</h4>
                        @if ($selectedShift->opening_notes)
                            <div class="p-3 mb-3 rounded-lg bg-base-200">
                                <div class="text-sm font-medium text-gray-700">Opening Notes:</div>
                                <div class="text-sm">{{ $selectedShift->opening_notes }}</div>
                            </div>
                        @endif
                        @if ($selectedShift->closing_notes)
                            <div class="p-3 rounded-lg bg-base-200">
                                <div class="text-sm font-medium text-gray-700">Closing Notes:</div>
                                <div class="text-sm">{{ $selectedShift->closing_notes }}</div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Sales List --}}
                @if ($selectedShift->sales->count() > 0)
                    <div>
                        <h4 class="mb-3 font-semibold">Sales Transactions ({{ $selectedShift->sales->count() }})</h4>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra table-sm">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Time</th>
                                        <th>Payment</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedShift->sales as $sale)
                                        <tr>
                                            <td>{{ $sale->invoice_number }}</td>
                                            <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                            <td>{{ $sale->created_at->format('H:i') }}</td>
                                            <td>
                                                <x-mary-badge value="{{ ucfirst($sale->payment_method) }}"
                                                    class="badge-{{ $sale->payment_method === 'cash' ? 'success' : 'info' }} badge-xs" />
                                            </td>
                                            <td class="font-semibold">₱{{ number_format($sale->total_amount, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                @if ($selectedShift->returns && $selectedShift->returns->count() > 0)
                    <div>
                        <h4 class="mb-3 font-semibold">Return Transactions ({{ $selectedShift->returns->count() }})
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra table-sm">
                                <thead>
                                    <tr>
                                        <th>Return #</th>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Time</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedShift->returns as $return)
                                        <tr>
                                            <td>{{ $return->return_number }}</td>
                                            <td>{{ $return->sale->invoice_number }}</td>
                                            <td>{{ $return->sale->customer?->name ?? 'Walk-in' }}</td>
                                            <td>{{ $return->created_at->format('H:i') }}</td>
                                            <td>
                                                <x-mary-badge value="{{ ucfirst($return->type) }}"
                                                    class="badge-{{ $return->type === 'refund' ? 'error' : ($return->type === 'exchange' ? 'info' : 'warning') }} badge-xs" />
                                            </td>
                                            <td class="font-semibold">₱{{ number_format($return->refund_amount, 2) }}
                                            </td>
                                            <td>
                                                <x-mary-badge value="{{ ucfirst($return->status) }}"
                                                    class="badge-{{ $return->status_color }} badge-xs" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button label="Close" wire:click="$set('showShiftDetailsModal', false)" />
            @if ($selectedShift)
                <x-mary-button label="Print Report" wire:click="printShiftReport({{ $selectedShift->id }})"
                    class="btn-primary" />
            @endif
        </x-slot:actions>
    </x-mary-modal>
</div>
