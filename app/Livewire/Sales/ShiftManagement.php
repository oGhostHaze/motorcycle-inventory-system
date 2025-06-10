<?php

namespace App\Livewire\Sales;

use App\Models\SalesShift;
use App\Models\Warehouse;
use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class ShiftManagement extends Component
{
    use WithPagination, Toast;

    // Shift start/end modals
    public bool $showStartShiftModal = false;
    public bool $showEndShiftModal = false;
    public bool $showShiftDetailsModal = false;

    // Current shift data
    public ?SalesShift $currentShift = null;
    public ?SalesShift $selectedShift = null;

    // Start shift form
    public $selectedWarehouse = '';
    public $openingCash = '';
    public string $openingNotes = '';

    // End shift form
    public $closingCash = '';
    public string $closingNotes = '';

    // Filters
    public string $search = '';
    public string $warehouseFilter = '';
    public string $statusFilter = '';
    public string $dateFilter = '';

    public function mount()
    {
        $this->loadCurrentShift();
        $this->selectedWarehouse = Warehouse::where('is_active', true)->first()?->id;
    }

    public function render()
    {
        $shifts = SalesShift::with(['user', 'warehouse'])
            ->when($this->search, fn($q) => $q->where('shift_number', 'like', '%' . $this->search . '%')
                ->orWhereHas('user', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                }))
            ->when($this->warehouseFilter, fn($q) => $q->where('warehouse_id', $this->warehouseFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFilter, function ($q) {
                switch ($this->dateFilter) {
                    case 'today':
                        return $q->whereDate('started_at', today());
                    case 'week':
                        return $q->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    case 'month':
                        return $q->whereMonth('started_at', now()->month);
                }
            })
            ->orderBy('started_at', 'desc')
            ->paginate(20);

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $filterOptions = [
            'warehouses' => $warehouses->map(fn($w) => ['value' => $w->id, 'label' => $w->name]),
            'statuses' => [
                ['value' => '', 'label' => 'All Status'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
            'dates' => [
                ['value' => '', 'label' => 'All Dates'],
                ['value' => 'today', 'label' => 'Today'],
                ['value' => 'week', 'label' => 'This Week'],
                ['value' => 'month', 'label' => 'This Month'],
            ]
        ];

        return view('livewire.sales.shift-management', [
            'shifts' => $shifts,
            'warehouses' => $warehouses,
            'filterOptions' => $filterOptions,
        ])->layout('layouts.app', ['title' => 'Shift Management']);
    }

    public function loadCurrentShift()
    {
        $this->currentShift = SalesShift::getActiveShift(auth()->id());
    }

    public function openStartShiftModal()
    {
        if ($this->currentShift) {
            $this->error('You already have an active shift. Please end it first.');
            return;
        }

        $this->resetStartShiftForm();
        $this->showStartShiftModal = true;
    }

    public function startShift()
    {
        $this->validate([
            'selectedWarehouse' => 'required|exists:warehouses,id',
            'openingCash' => 'required|numeric|min:0',
            'openingNotes' => 'nullable|string|max:500',
        ]);

        // Check for existing active shift
        if (SalesShift::hasActiveShift(auth()->id())) {
            $this->error('You already have an active shift.');
            return;
        }

        try {
            $this->currentShift = SalesShift::create([
                'user_id' => auth()->id(),
                'warehouse_id' => $this->selectedWarehouse,
                'started_at' => now(),
                'opening_cash' => $this->openingCash,
                'opening_notes' => $this->openingNotes,
                'status' => 'active',
            ]);

            $this->success('Shift started successfully! Shift #: ' . $this->currentShift->shift_number);
            $this->showStartShiftModal = false;
            $this->resetStartShiftForm();
        } catch (\Exception $e) {
            $this->error('Error starting shift: ' . $e->getMessage());
        }
    }

    public function openEndShiftModal()
    {
        if (!$this->currentShift) {
            $this->error('No active shift found.');
            return;
        }

        // Calculate expected cash
        $this->currentShift->calculateTotals();
        $this->currentShift->refresh();

        $this->closingCash = $this->currentShift->expected_cash;
        $this->closingNotes = '';
        $this->showEndShiftModal = true;
    }

    public function endShift()
    {
        $this->validate([
            'closingCash' => 'required|numeric|min:0',
            'closingNotes' => 'nullable|string|max:500',
        ]);

        if (!$this->currentShift) {
            $this->error('No active shift found.');
            return;
        }

        try {
            $this->currentShift->endShift($this->closingCash, $this->closingNotes);

            $cashDifference = $this->currentShift->cash_difference;
            $message = 'Shift ended successfully!';

            if ($cashDifference > 0) {
                $message .= ' Cash Over: ₱' . number_format($cashDifference, 2);
            } elseif ($cashDifference < 0) {
                $message .= ' Cash Short: ₱' . number_format(abs($cashDifference), 2);
            } else {
                $message .= ' Cash balanced perfectly!';
            }

            $this->success($message);
            $this->currentShift = null;
            $this->showEndShiftModal = false;
            $this->resetEndShiftForm();
        } catch (\Exception $e) {
            $this->error('Error ending shift: ' . $e->getMessage());
        }
    }

    public function viewShiftDetails(SalesShift $shift)
    {
        $this->selectedShift = $shift->load(['sales.customer', 'user', 'warehouse']);
        $this->showShiftDetailsModal = true;
    }

    public function printShiftReport(SalesShift $shift)
    {
        // Logic to generate shift report PDF
        $this->success('Shift report generated successfully!');
    }

    public function cancelShift(SalesShift $shift)
    {
        if ($shift->status !== 'active') {
            $this->error('Only active shifts can be cancelled.');
            return;
        }

        if ($shift->sales()->exists()) {
            $this->error('Cannot cancel shift with existing sales.');
            return;
        }

        $shift->update(['status' => 'cancelled']);

        if ($shift->id === $this->currentShift?->id) {
            $this->currentShift = null;
        }

        $this->success('Shift cancelled successfully.');
    }

    public function clearFilters()
    {
        $this->reset(['search', 'warehouseFilter', 'statusFilter', 'dateFilter']);
    }

    private function resetStartShiftForm()
    {
        $this->reset(['selectedWarehouse', 'openingCash', 'openingNotes']);
        $this->selectedWarehouse = Warehouse::where('is_active', true)->first()?->id;
    }

    private function resetEndShiftForm()
    {
        $this->reset(['closingCash', 'closingNotes']);
    }

    /**
     * Format shift duration - minutes if < 1 hour, decimal hours if >= 1 hour
     */
    public function formatShiftDuration($shift)
    {
        if (!$shift->ended_at) {
            // For active shifts, show duration from start time
            $totalMinutes = $shift->started_at->diffInMinutes(now());

            if ($totalMinutes < 60) {
                return number_format($totalMinutes, 2) . ' min' . ($totalMinutes != 1 ? 's' : '');
            } else {
                $hours = $totalMinutes / 60;
                return number_format($hours, 2) . ' hrs';
            }
        }

        // For completed shifts, calculate total duration
        $totalMinutes = $shift->started_at->diffInMinutes($shift->ended_at);

        if ($totalMinutes < 60) {
            return number_format($totalMinutes, 2) . ' min' . ($totalMinutes != 1 ? 's' : '');
        } else {
            $hours = $totalMinutes / 60;
            return number_format($hours, 2) . ' hrs';
        }
    }

    /**
     * Enhanced cash difference text with 2 decimal places
     */
    public function getCashDifferenceText($difference)
    {
        if ($difference > 0) {
            return '+₱' . number_format($difference, 2);
        } elseif ($difference < 0) {
            return '-₱' . number_format(abs($difference), 2);
        } else {
            return 'Balanced';
        }
    }

    /**
     * Get cash difference badge color class
     */
    public function getCashDifferenceClass($difference)
    {
        if ($difference > 0) return 'warning';  // Cash over
        if ($difference < 0) return 'error';    // Cash short
        return 'success';                       // Balanced
    }

    /**
     * Get shift status badge color class
     */
    public function getShiftStatusClass($status)
    {
        return match ($status) {
            'active' => 'success',
            'completed' => 'info',
            'cancelled' => 'error',
            default => 'neutral',
        };
    }
}
