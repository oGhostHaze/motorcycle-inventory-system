<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CycleCountItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle_count_id',
        'product_id',
        'product_variant_id',
        'system_quantity',
        'counted_quantity',
        'variance',
        'unit_cost',
        'variance_value',
        'notes',
        'counted_by',
        'counted_at'
    ];

    protected $casts = [
        'system_quantity' => 'integer',
        'counted_quantity' => 'integer',
        'variance' => 'integer',
        'unit_cost' => 'decimal:2',
        'variance_value' => 'decimal:2',
        'counted_at' => 'datetime',
    ];

    public function cycleCount()
    {
        return $this->belongsTo(CycleCount::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function countedBy()
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (!is_null($model->counted_quantity) && !is_null($model->system_quantity)) {
                $model->variance = $model->counted_quantity - $model->system_quantity;
                if (!is_null($model->unit_cost)) {
                    $model->variance_value = $model->variance * $model->unit_cost;
                }
            }
        });
    }
}
