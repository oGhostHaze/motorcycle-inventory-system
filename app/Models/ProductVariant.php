<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'barcode',
        'price_adjustment',
        'attributes',
        'is_active'
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function getFinalPriceAttribute()
    {
        return $this->product->selling_price + $this->price_adjustment;
    }
}
