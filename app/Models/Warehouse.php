<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'address',
        'city',
        'manager_name',
        'phone',
        'type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->slug = Str::slug($model->name);
            if (empty($model->code)) {
                $model->code = strtoupper(Str::random(6));
            }
        });
    }
}
