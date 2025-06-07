<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_number',
        'customer_id',
        'product_id',
        'serial_number_id',
        'sale_id',
        'purchase_date',
        'claim_date',
        'issue_description',
        'status',
        'resolution_type',
        'claim_amount',
        'resolution_notes',
        'handled_by',
        'resolved_at'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'claim_date' => 'date',
        'claim_amount' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function serialNumber()
    {
        return $this->belongsTo(SerialNumber::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->claim_number)) {
                $model->claim_number = 'WC-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
