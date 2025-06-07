<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'category',
        'data',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public static function createLowStockAlert($product, $warehouse, $currentStock)
    {
        return static::create([
            'user_id' => null, // System notification
            'title' => 'Low Stock Alert',
            'message' => "Product {$product->name} is running low in {$warehouse->name}. Current stock: {$currentStock}",
            'type' => 'warning',
            'category' => 'low_stock',
            'data' => [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'current_stock' => $currentStock,
                'min_stock_level' => $product->min_stock_level,
            ],
        ]);
    }
}
