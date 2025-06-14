<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_number',
        'user_id',
        'warehouse_id',
        'started_at',
        'ended_at',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_sales',
        'cash_sales',
        'card_sales',
        'other_sales',
        'total_transactions',
        'opening_notes',
        'closing_notes',
        'status',
        'total_returns_count',
        'total_returns_amount',
        'processed_returns_count',
        'processed_returns_amount',
        'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'other_sales' => 'decimal:2',
        'total_transactions' => 'integer',
        'total_returns_count' => 'integer',
        'total_returns_amount' => 'decimal:2',
        'processed_returns_count' => 'integer',
        'processed_returns_amount' => 'decimal:2',
    ];


    public function returns()
    {
        return $this->hasMany(SaleReturn::class, 'sales_shift_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'shift_id');
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active' && !$this->ended_at;
    }

    public function getExpectedCashCalculatedAttribute()
    {
        return $this->opening_cash + $this->cash_sales;
    }

    public function calculateTotals()
    {
        $baseQuery = $this->sales()->where('status', 'completed');

        $this->update([
            'total_transactions' => $baseQuery->count(),
            'total_sales' => $baseQuery->sum('total_amount'),
            'cash_sales' => (clone $baseQuery)->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => (clone $baseQuery)->where('payment_method', 'card')->sum('total_amount'),
            'other_sales' => (clone $baseQuery)->whereNotIn('payment_method', ['cash', 'card'])->sum('total_amount'),
        ]);

        // Calculate expected cash
        $this->update([
            'expected_cash' => $this->expected_cash_calculated
        ]);
    }

    public function endShift($closingCash, $closingNotes = null)
    {
        $this->calculateTotals();

        $this->update([
            'ended_at' => now(),
            'closing_cash' => $closingCash,
            'cash_difference' => $closingCash - $this->expected_cash,
            'closing_notes' => $closingNotes,
            'status' => 'completed'
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->shift_number)) {
                $model->shift_number = 'SH-' . date('Ymd') . '-' . str_pad(
                    static::whereDate('started_at', today())->count() + 1,
                    3,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    public static function getActiveShift($userId, $warehouseId = null)
    {
        $query = static::where('user_id', $userId)
            ->where('status', 'active')
            ->whereNull('ended_at');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->first();
    }

    public static function hasActiveShift($userId, $warehouseId = null)
    {
        return static::getActiveShift($userId, $warehouseId) !== null;
    }


    public function getShiftDurationAttribute()
    {
        if (!$this->ended_at) {
            $minutes = $this->started_at->diffInMinutes(now());

            if ($minutes < 60) {
                return $minutes . ' min' . ($minutes != 1 ? 's' : '');
            } else {
                $hours = floor($minutes / 60);
                $remainingMinutes = $minutes % 60;

                if ($remainingMinutes == 0) {
                    return $hours . ' hr' . ($hours != 1 ? 's' : '');
                } else {
                    return $hours . 'h ' . $remainingMinutes . 'm';
                }
            }
        }

        $totalMinutes = $this->started_at->diffInMinutes($this->ended_at);

        if ($totalMinutes < 60) {
            return $totalMinutes . ' min' . ($totalMinutes != 1 ? 's' : '');
        } else {
            $hours = floor($totalMinutes / 60);
            $remainingMinutes = $totalMinutes % 60;

            if ($remainingMinutes == 0) {
                return $hours . ' hr' . ($hours != 1 ? 's' : '');
            } else {
                return $hours . 'h ' . $remainingMinutes . 'm';
            }
        }
    }

    public function getPendingReturnsCountAttribute()
    {
        return $this->returns()->where('status', 'pending')->count();
    }

    public function getProcessedReturnsCountAttribute()
    {
        return $this->returns()->where('status', 'processed')->count();
    }

    public function getTotalReturnAmountAttribute()
    {
        return $this->returns()->where('status', 'processed')->sum('refund_amount');
    }
    // In SalesShift model - Add proper calculated attributes (FIXED column names)

    public function getActualRefundedAmountAttribute()
    {
        // Only count processed returns for actual refunded amount
        return $this->returns()
            ->where('status', 'processed')
            ->where('type', 'refund')
            ->sum('refund_amount');
    }

    public function getPendingReturnAmountAttribute()
    {
        // Amount tied up in pending returns (not yet refunded)
        return $this->returns()
            ->where('status', 'pending')
            ->sum('refund_amount');
    }

    public function getProcessedReturnCountAttribute()
    {
        return $this->returns()
            ->where('status', 'processed')
            ->count();
    }

    public function getAdjustedCashSalesAttribute()
    {
        // Cash sales minus actual cash refunds
        $cashRefunds = $this->returns()
            ->where('status', 'processed')
            ->where('type', 'refund')
            ->whereHas('sale', function ($q) {
                $q->where('payment_method', 'cash');
            })
            ->sum('refund_amount');

        return $this->cash_sales - $cashRefunds;
    }

    public function getNetSalesAttribute()
    {
        // Total sales minus actual processed refunds
        return $this->total_sales - $this->actual_refunded_amount;
    }
}
