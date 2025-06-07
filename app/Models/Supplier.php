<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'rating',
        'lead_time_days',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'lead_time_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }
}
