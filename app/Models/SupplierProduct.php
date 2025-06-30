<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'product_id',
        'supplier_sku',
        'supplier_price',
        'minimum_order_quantity',
        'lead_time_days',
        'is_preferred',
        'is_active',
        'notes',
        'last_ordered_at'
    ];

    protected $casts = [
        'supplier_price' => 'decimal:2',
        'minimum_order_quantity' => 'integer',
        'lead_time_days' => 'integer',
        'is_preferred' => 'boolean',
        'is_active' => 'boolean',
        'last_ordered_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
