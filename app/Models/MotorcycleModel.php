<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MotorcycleModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'name',
        'slug',
        'engine_type',
        'engine_cc',
        'year_from',
        'year_to',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'year_from' => 'integer',
        'year_to' => 'integer',
    ];

    public function brand()
    {
        return $this->belongsTo(MotorcycleBrand::class);
    }

    public function compatibleProducts()
    {
        return $this->belongsToMany(Product::class, 'product_compatibility', 'motorcycle_model_id', 'product_id')
            ->withPivot('year_from', 'year_to', 'notes')
            ->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }
}
