<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;


    protected $fillable = [
        'product_id',
        'old_cost_price',
        'new_cost_price',
        'old_selling_price',
        'new_selling_price',
        'old_wholesale_price',
        'new_wholesale_price',
        'reason',
        'changed_by'
    ];

    protected $casts = [
        'old_cost_price' => 'decimal:2',
        'new_cost_price' => 'decimal:2',
        'old_selling_price' => 'decimal:2',
        'new_selling_price' => 'decimal:2',
        'old_wholesale_price' => 'decimal:2',
        'new_wholesale_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public static function logPriceChange($product, $oldValues, $newValues, $reason = null)
    {
        return static::create([
            'product_id' => $product->id,
            'old_cost_price' => $oldValues['cost_price'] ?? 0,
            'new_cost_price' => $newValues['cost_price'] ?? 0,
            'old_selling_price' => $oldValues['selling_price'] ?? 0,
            'new_selling_price' => $newValues['selling_price'] ?? 0,
            'old_wholesale_price' => $oldValues['wholesale_price'] ?? null,
            'new_wholesale_price' => $newValues['wholesale_price'] ?? null,
            'reason' => $reason,
            'changed_by' => auth()->id(),
        ]);
    }
}
