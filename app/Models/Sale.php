<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'promotion_id',
        'promotion_code',
        'warehouse_id',
        'user_id',
        'shift_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'status',
        'notes',
        'completed_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Added shift relationship
    public function shift()
    {
        return $this->belongsTo(SalesShift::class, 'shift_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function warrantyClaims()
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->invoice_number)) {
                $model->invoice_number = 'INV-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
