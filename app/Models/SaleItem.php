<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'quantity',
        'returned_quantity',
        'unit_price',
        'discount_amount',
        'total_price',
        'cost_price',
        'serial_numbers',
        'service_id',
        'item_type', // 'product' or 'service'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'returned_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'serial_numbers' => 'array',
        'item_type' => 'string',
    ];

    // ========== RELATIONSHIPS ==========

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function service()
    {
        return $this->belongsTo(ProductService::class, 'service_id');
    }

    // ========== SMART RELATIONSHIPS ==========

    /**
     * Get the item (product or service) associated with this sale item
     * This replaces the need to check item_type everywhere
     */
    public function item()
    {
        if ($this->isService()) {
            return $this->service();
        }
        return $this->product();
    }

    /**
     * Get the item instance (product or service)
     */
    public function getItemAttribute()
    {
        if ($this->isService()) {
            return $this->service;
        }
        return $this->product;
    }

    // ========== SMART ACCESSORS ==========

    /**
     * Get item name - works for both products and services
     * This maintains backward compatibility with existing code
     */
    public function getNameAttribute()
    {
        if ($this->isService() && $this->service) {
            return $this->service->name;
        }

        if ($this->product) {
            return $this->product->name;
        }

        // Fallback to stored name
        return $this->product_name ?? 'Unknown Item';
    }

    /**
     * Get item code/SKU - works for both products and services
     */
    public function getCodeAttribute()
    {
        if ($this->isService() && $this->service) {
            return $this->service->code;
        }

        if ($this->product) {
            return $this->product->sku;
        }

        // Fallback to stored SKU
        return $this->product_sku ?? 'N/A';
    }

    /**
     * Get item price - works for both products and services
     */
    public function getItemPriceAttribute()
    {
        if ($this->isService() && $this->service) {
            return $this->service->price;
        }

        if ($this->product) {
            return $this->product->selling_price;
        }

        // Fallback to stored price
        return $this->unit_price ?? 0;
    }

    // ========== TYPE CHECKING METHODS ==========

    public function isService()
    {
        return $this->item_type === 'service';
    }

    public function isProduct()
    {
        return $this->item_type === 'product' || $this->item_type === null; // null for backward compatibility
    }

    public function hasSerialTracking()
    {
        return $this->isProduct() && $this->product && $this->product->track_serial;
    }

    // ========== BACKWARD COMPATIBILITY METHODS ==========

    /**
     * Override the product relationship to be smart
     * This ensures existing code that calls $saleItem->product still works
     */
    public function getProductAttribute()
    {
        if ($this->isService()) {
            // Return a pseudo-product object for services to maintain compatibility
            return (object) [
                'id' => null,
                'name' => $this->service ? $this->service->name : $this->product_name,
                'sku' => $this->service ? $this->service->code : $this->product_sku,
                'selling_price' => $this->unit_price,
                'track_serial' => false,
                'is_service' => true,
            ];
        }

        // Load the actual product relationship
        return $this->getRelationValue('product') ?? Product::find($this->product_id);
    }

    // ========== SCOPES ==========

    public function scopeProducts($query)
    {
        return $query->where('item_type', 'product')->orWhereNull('item_type');
    }

    public function scopeServices($query)
    {
        return $query->where('item_type', 'service');
    }

    // ========== MODEL EVENTS ==========

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-detect item type if not set
            if (!$model->item_type) {
                if ($model->service_id) {
                    $model->item_type = 'service';
                } else {
                    $model->item_type = 'product';
                }
            }

            // Set product_name and product_sku if not provided
            if (!$model->product_name || !$model->product_sku) {
                if ($model->isService() && $model->service_id) {
                    $service = ProductService::find($model->service_id);
                    if ($service) {
                        $model->product_name = $model->product_name ?: $service->name;
                        $model->product_sku = $model->product_sku ?: $service->code;
                    }
                } elseif ($model->product_id) {
                    $product = Product::find($model->product_id);
                    if ($product) {
                        $model->product_name = $model->product_name ?: $product->name;
                        $model->product_sku = $model->product_sku ?: $product->sku;
                    }
                }
            }
        });
    }

    // ========== HELPER METHODS ==========

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '₱' . number_format($this->unit_price, 2);
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute()
    {
        return '₱' . number_format($this->total_price, 2);
    }

    /**
     * Get profit margin (for products only)
     */
    public function getProfitAttribute()
    {
        if ($this->isService()) {
            return $this->total_price; // Services have no cost
        }

        return $this->total_price - ($this->cost_price * $this->quantity);
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMarginAttribute()
    {
        if ($this->total_price == 0) {
            return 0;
        }

        return ($this->profit / $this->total_price) * 100;
    }

    public function returnItems()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    // Accessors and Helper Methods
    public function getAvailableToReturnAttribute()
    {
        return $this->quantity - $this->returned_quantity;
    }

    public function getIsFullyReturnedAttribute()
    {
        return $this->returned_quantity >= $this->quantity;
    }

    public function getReturnPercentageAttribute()
    {
        return $this->quantity > 0 ? ($this->returned_quantity / $this->quantity) * 100 : 0;
    }

    // Methods
    public function canBeReturned($requestedQuantity = 1)
    {
        return ($this->returned_quantity + $requestedQuantity) <= $this->quantity;
    }

    public function incrementReturnedQuantity($quantity)
    {
        $this->increment('returned_quantity', $quantity);
    }

    public function decrementReturnedQuantity($quantity)
    {
        $this->decrement('returned_quantity', $quantity);
    }

    public function getItemNameAttribute()
    {
        if ($this->item_type === 'service' && $this->service) {
            return $this->service->name;
        }

        return $this->product ? $this->product->name : $this->product_name ?? 'Unknown Item';
    }

    public function getItemCodeAttribute()
    {
        if ($this->item_type === 'service' && $this->service) {
            return $this->service->code;
        }

        return $this->product ? $this->product->sku : $this->product_sku ?? 'N/A';
    }
}
