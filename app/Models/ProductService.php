<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'price',
        'status',
        'notes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'service_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getFormattedPriceAttribute()
    {
        return '₱' . number_format($this->price, 2);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
            if (empty($model->code)) {
                $model->code = 'SVC-' . strtoupper(Str::random(6));
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }
}
