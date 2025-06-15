<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'warehouse_id',
        'requested_by',
        'status',
        'total_amount',
        'order_date',
        'expected_date',
        'received_date',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'order_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
    ];

    // ========== RELATIONSHIPS ==========

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get stock movements related to this purchase order
     */
    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    // ========== COMPUTED ATTRIBUTES ==========

    /**
     * Get the status color for badges
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'draft' => 'neutral',
            'pending' => 'warning',
            'partial' => 'info',
            'completed' => 'success',
            'cancelled' => 'error',
            default => 'neutral',
        };
    }

    /**
     * Get the total quantity ordered
     */
    public function getTotalQuantityOrderedAttribute()
    {
        return $this->items->sum('quantity_ordered');
    }

    /**
     * Get the total quantity received
     */
    public function getTotalQuantityReceivedAttribute()
    {
        return $this->items->sum('quantity_received');
    }

    /**
     * Get the total quantity pending
     */
    public function getTotalQuantityPendingAttribute()
    {
        return $this->total_quantity_ordered - $this->total_quantity_received;
    }

    /**
     * Get the completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->total_quantity_ordered == 0) {
            return 0;
        }
        return ($this->total_quantity_received / $this->total_quantity_ordered) * 100;
    }

    /**
     * Check if the PO is overdue
     */
    public function getIsOverdueAttribute()
    {
        return $this->expected_date &&
            $this->expected_date->isPast() &&
            !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Get formatted status display
     */
    public function getStatusDisplayAttribute()
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending' => 'Pending',
            'partial' => 'Partially Received',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    // ========== SCOPES ==========

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by supplier
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope to filter by warehouse
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope for overdue orders
     */
    public function scopeOverdue($query)
    {
        return $query->where('expected_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope for active orders (not completed or cancelled)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    // ========== HELPER METHODS ==========

    /**
     * Check if the PO can be edited
     */
    public function canBeEdited()
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the PO can be submitted
     */
    public function canBeSubmitted()
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the PO can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    /**
     * Check if the PO can receive items
     */
    public function canReceiveItems()
    {
        return in_array($this->status, ['pending', 'partial']);
    }

    /**
     * Submit the purchase order
     */
    public function submit()
    {
        if (!$this->canBeSubmitted()) {
            throw new \Exception('Purchase order cannot be submitted in current status.');
        }

        $this->update(['status' => 'pending']);
        return $this;
    }

    /**
     * Cancel the purchase order
     */
    public function cancel()
    {
        if (!$this->canBeCancelled()) {
            throw new \Exception('Purchase order cannot be cancelled in current status.');
        }

        $this->update(['status' => 'cancelled']);
        return $this;
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'received_date' => now(),
        ]);
        return $this;
    }

    // ========== MODEL EVENTS ==========

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->po_number)) {
                $model->po_number = 'PO-' . date('Ymd') . '-' . str_pad(
                    PurchaseOrder::whereDate('created_at', today())->count() + 1,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });

        static::updated(function ($model) {
            // Auto-update status based on received quantities
            if ($model->isDirty('status') && in_array($model->status, ['pending', 'partial'])) {
                $totalOrdered = $model->items->sum('quantity_ordered');
                $totalReceived = $model->items->sum('quantity_received');

                if ($totalReceived >= $totalOrdered && $totalOrdered > 0) {
                    $model->update(['status' => 'completed', 'received_date' => now()]);
                } elseif ($totalReceived > 0) {
                    $model->update(['status' => 'partial']);
                }
            }
        });
    }
}
