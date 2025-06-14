<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_return_id',
        'sale_item_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'reason',
        'condition',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getConditionColorAttribute()
    {
        return match ($this->condition) {
            'good' => 'success',
            'damaged' => 'warning',
            'defective' => 'error',
            default => 'ghost',
        };
    }

    public function getConditionDisplayAttribute()
    {
        return match ($this->condition) {
            'good' => 'Good',
            'damaged' => 'Damaged',
            'defective' => 'Defective',
            default => ucfirst($this->condition),
        };
    }

    public function getReasonDisplayAttribute()
    {
        return match ($this->reason) {
            'defective' => 'Defective Product',
            'wrong_item' => 'Wrong Item',
            'not_as_described' => 'Not as Described',
            'customer_changed_mind' => 'Customer Changed Mind',
            'damaged_shipping' => 'Damaged in Shipping',
            'warranty_claim' => 'Warranty Claim',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->reason)),
        };
    }
}
