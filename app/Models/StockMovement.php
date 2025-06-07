<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'product_variant_id',
        'type',
        'quantity_before',
        'quantity_changed',
        'quantity_after',
        'unit_cost',
        'reference_id',
        'reference_type',
        'user_id',
        'notes'
    ];

    protected $casts = [
        'quantity_before' => 'integer',
        'quantity_changed' => 'integer',
        'quantity_after' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
