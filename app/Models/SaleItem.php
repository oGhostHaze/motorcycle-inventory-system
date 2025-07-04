<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'quantity',
        'returned_quantity',
        'unit_price',
        'discount_amount',
        'total_price',
        'cost_price',
        'serial_numbers',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'returned_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'serial_numbers' => 'array',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function returnItems()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    // Accessors and Helper Methods
    public function getAvailableToReturnAttribute()
    {
        return $this->quantity - $this->returned_quantity;
    }

    public function getIsFullyReturnedAttribute()
    {
        return $this->returned_quantity >= $this->quantity;
    }

    public function getReturnPercentageAttribute()
    {
        return $this->quantity > 0 ? ($this->returned_quantity / $this->quantity) * 100 : 0;
    }

    // Methods
    public function canBeReturned($requestedQuantity = 1)
    {
        return ($this->returned_quantity + $requestedQuantity) <= $this->quantity;
    }

    public function incrementReturnedQuantity($quantity)
    {
        $this->increment('returned_quantity', $quantity);
    }

    public function decrementReturnedQuantity($quantity)
    {
        $this->decrement('returned_quantity', $quantity);
    }
}
