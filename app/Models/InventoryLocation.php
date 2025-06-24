<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'zone',
        'section',
        'level',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByZone($query, $zone)
    {
        return $query->where('zone', $zone);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('zone')
            ->orderBy('section')
            ->orderBy('level')
            ->orderBy('sort_order')
            ->orderBy('code');
    }

    // Helper methods
    public function getFullLocationAttribute()
    {
        $parts = array_filter([$this->zone, $this->section, $this->level]);
        return implode('-', $parts) ?: $this->code;
    }

    public function isOccupied()
    {
        return $this->inventories()->where('quantity_on_hand', '>', 0)->exists();
    }

    public function getTotalQuantity()
    {
        return $this->inventories()->sum('quantity_on_hand');
    }

    public function getProductCount()
    {
        return $this->inventories()->distinct('product_id')->count();
    }

    // Model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->sort_order)) {
                $model->sort_order = static::max('sort_order') + 1;
            }
        });
    }
}
