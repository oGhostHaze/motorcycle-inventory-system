<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'product_variant_id',
        'quantity_on_hand',
        'quantity_reserved',
        'average_cost',
        'location',
        'last_counted_at'
    ];

    protected $casts = [
        'quantity_on_hand' => 'integer',
        'quantity_reserved' => 'integer',
        'average_cost' => 'decimal:2',
        'last_counted_at' => 'date',
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

    public function getQuantityAvailableAttribute()
    {
        return $this->quantity_on_hand - $this->quantity_reserved;
    }

    // Add this relationship to the existing Inventory model
    public function inventoryLocation()
    {
        return $this->belongsTo(InventoryLocation::class);
    }

    // Add this accessor for backward compatibility
    public function getLocationAttribute()
    {
        // If new location system is used, return the location name
        if ($this->inventoryLocation) {
            return $this->inventoryLocation->name;
        }

        // Otherwise, return legacy location
        return $this->location_legacy;
    }

    // Add this accessor for location code
    public function getLocationCodeAttribute()
    {
        return $this->inventoryLocation?->code ?? $this->location_legacy;
    }

    // Add helper method
    public function hasLocation()
    {
        return $this->inventory_location_id || $this->location_legacy;
    }
}
