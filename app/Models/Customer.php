<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'type',
        'customer_group_id',
        'date_of_birth',
        'gender',
        'tax_id',
        'credit_limit',
        'total_purchases',
        'total_orders',
        'last_purchase_at',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'credit_limit' => 'decimal:2',
        'total_purchases' => 'decimal:2',
        'total_orders' => 'integer',
        'last_purchase_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class, 'sold_to_customer_id');
    }

    public function warrantyClaims()
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function getApplicableDiscountAttribute()
    {
        return $this->customerGroup?->discount_percentage ?? 0;
    }
}
