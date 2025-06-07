<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'product_variant_id',
        'quantity_shipped',
        'quantity_received',
        'unit_cost',
        'serial_numbers',
        'notes'
    ];

    protected $casts = [
        'quantity_shipped' => 'integer',
        'quantity_received' => 'integer',
        'unit_cost' => 'decimal:2',
        'serial_numbers' => 'array',
    ];

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getQuantityPendingAttribute()
    {
        return $this->quantity_shipped - $this->quantity_received;
    }
}
