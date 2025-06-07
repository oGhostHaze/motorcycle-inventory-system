<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerialNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'serial_number',
        'status',
        'manufactured_date',
        'warranty_expires_at',
        'sold_to_customer_id',
        'sold_at',
        'notes'
    ];

    protected $casts = [
        'manufactured_date' => 'date',
        'warranty_expires_at' => 'date',
        'sold_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'sold_to_customer_id');
    }

    public function isUnderWarranty()
    {
        return $this->warranty_expires_at && $this->warranty_expires_at->isFuture();
    }
}
