<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'subcategory_id',
        'product_brand_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'rfid_tag',
        'description',
        'specifications',
        'part_number',
        'oem_number',
        'cost_price',
        'selling_price',
        'wholesale_price',
        'alt_price1',
        'alt_price2',
        'alt_price3',
        'warranty_months',
        'track_serial',
        'track_warranty',
        'min_stock_level',
        'max_stock_level',
        'reorder_point',
        'reorder_quantity',
        'status',
        'images',
        'internal_notes'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'alt_price1' => 'decimal:2',
        'alt_price2' => 'decimal:2',
        'alt_price3' => 'decimal:2',
        'warranty_months' => 'integer',
        'track_serial' => 'boolean',
        'track_warranty' => 'boolean',
        'min_stock_level' => 'integer',
        'max_stock_level' => 'integer',
        'reorder_point' => 'integer',
        'reorder_quantity' => 'integer',
        'specifications' => 'array',
        'images' => 'array',
    ];

    // ========== RELATIONSHIPS ==========

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(ProductBrand::class, 'product_brand_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function completedSaleItems()
    {
        return $this->hasMany(SaleItem::class)->whereHas('sale', function ($query) {
            $query->where('status', 'completed');
        });
    }

    public function sales()
    {
        return $this->hasManyThrough(Sale::class, SaleItem::class, 'product_id', 'id', 'id', 'sale_id');
    }

    public function compatibleModels()
    {
        return $this->belongsToMany(MotorcycleModel::class, 'product_compatibility')
            ->withPivot('year_from', 'year_to', 'notes')
            ->withTimestamps();
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function priceHistory()
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function warrantyClaims()
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    // ========== COMPUTED ATTRIBUTES ==========

    public function getTotalStockAttribute()
    {
        return $this->inventory()->sum('quantity_on_hand');
    }

    public function getAvailableStockAttribute()
    {
        return $this->inventory()->sum('quantity_available');
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->where('is_approved', true)->avg('rating') ?? 0;
    }

    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->where('is_approved', true)->count();
    }

    public function getTotalSoldAttribute()
    {
        return $this->completedSaleItems()->sum('quantity');
    }

    public function getTotalRevenueAttribute()
    {
        return $this->completedSaleItems()->sum('total_price');
    }

    public function getPreferredSupplierAttribute()
    {
        return $this->supplierProducts()
            ->where('is_preferred', true)
            ->where('is_active', true)
            ->with('supplier')
            ->first()?->supplier;
    }

    // ========== HELPER METHODS ==========
    public function getAvailablePrices()
    {
        $prices = [
            'selling_price' => [
                'label' => 'Selling Price',
                'value' => $this->selling_price
            ]
        ];

        if ($this->wholesale_price) {
            $prices['wholesale_price'] = [
                'label' => 'Wholesale Price',
                'value' => $this->wholesale_price
            ];
        }

        if ($this->alt_price1) {
            $prices['alt_price1'] = [
                'label' => 'Alternative Price 1',
                'value' => $this->alt_price1
            ];
        }

        if ($this->alt_price2) {
            $prices['alt_price2'] = [
                'label' => 'Alternative Price 2',
                'value' => $this->alt_price2
            ];
        }

        if ($this->alt_price3) {
            $prices['alt_price3'] = [
                'label' => 'Alternative Price 3',
                'value' => $this->alt_price3
            ];
        }

        return $prices;
    }

    public function isLowStock()
    {
        return $this->total_stock <= $this->min_stock_level;
    }

    /**
     * Safely log price changes - only if price_histories table exists
     */
    public static function logPriceChange($product, $oldValues, $newValues, $reason = null)
    {
        try {
            // Check if price_histories table exists and PriceHistory model is available
            if (Schema::hasTable('price_histories') && class_exists(\App\Models\PriceHistory::class)) {
                \App\Models\PriceHistory::logPriceChange($product, $oldValues, $newValues, $reason);
            }
        } catch (\Exception $e) {
            // Silently fail if price history logging fails
            // This prevents price updates from failing due to missing table
            \Log::warning('Price history logging failed: ' . $e->getMessage());
        }
    }

    // ========== MODEL EVENTS ==========

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
            if (empty($model->sku)) {
                $model->sku = 'SKU-' . strtoupper(Str::random(8));
            }
        });

        static::updating(function ($model) {
            // Safely log price changes
            if ($model->isDirty(['cost_price', 'selling_price', 'wholesale_price', 'alt_price1', 'alt_price2', 'alt_price3'])) {
                try {
                    $oldValues = $model->getOriginal();
                    $newValues = $model->getAttributes();

                    // Use the safe logging method
                    static::logPriceChange($model, $oldValues, $newValues, 'Product update');
                } catch (\Exception $e) {
                    // Don't let price history logging failure prevent product updates
                    \Log::warning('Price history logging failed during product update: ' . $e->getMessage());
                }
            }
        });
    }
}
