<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'sale_id',
        'customer_id',
        'warehouse_id',
        'user_id',
        'sales_shift_id',
        'type',
        'reason',
        'notes',
        'refund_amount',
        'restock_condition',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'processed_by',
        'processed_at',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'processed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function salesShift()
    {
        return $this->belongsTo(SalesShift::class, 'sales_shift_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function cancelledBy()  // Added
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCancelled($query)  // Added
    {
        return $query->where('status', 'cancelled');
    }

    // Accessors
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'processed' => 'success',
            'rejected' => 'error',
            'cancelled' => 'ghost',  // Added
            default => 'ghost',
        };
    }

    public function getTypeDisplayAttribute()
    {
        return match ($this->type) {
            'refund' => 'Refund',
            'exchange' => 'Exchange',
            'store_credit' => 'Store Credit',
            default => ucfirst($this->type),
        };
    }

    public function getReasonDisplayAttribute()
    {
        return match ($this->reason) {
            'defective' => 'Defective Product',
            'wrong_item' => 'Wrong Item',
            'not_as_described' => 'Not as Described',
            'customer_changed_mind' => 'Customer Changed Mind',
            'damaged_shipping' => 'Damaged in Shipping',
            'warranty_claim' => 'Warranty Claim',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->reason)),
        };
    }

    // Helper methods
    public function canBeApproved()
    {
        return $this->status === 'pending';
    }

    public function canBeProcessed()
    {
        return $this->status === 'approved';
    }

    public function canBeRejected()
    {
        return $this->status === 'pending';
    }

    public function canBeCancelled()  // Added
    {
        return $this->status === 'processed';
    }

    public function getTotalItemsCount()
    {
        return $this->items->sum('quantity');
    }
}
