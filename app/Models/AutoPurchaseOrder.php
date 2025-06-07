<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoPurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'warehouse_id',
        'status',
        'suggested_date',
        'products',
        'estimated_total',
        'generated_po_id'
    ];

    protected $casts = [
        'suggested_date' => 'date',
        'products' => 'array',
        'estimated_total' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function generatedPurchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'generated_po_id');
    }
}
