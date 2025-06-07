<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'discount_value',
        'minimum_amount',
        'usage_limit',
        'usage_count',
        'starts_at',
        'expires_at',
        'is_active',
        'applicable_products',
        'applicable_categories'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'starts_at' => 'date',
        'expires_at' => 'date',
        'is_active' => 'boolean',
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function isValid()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now()->toDateString();
        if ($now < $this->starts_at || $now > $this->expires_at) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount($amount, $products = [])
    {
        if (!$this->isValid()) {
            return 0;
        }

        if ($this->minimum_amount && $amount < $this->minimum_amount) {
            return 0;
        }

        // Check if products are applicable (if specified)
        if ($this->applicable_products || $this->applicable_categories) {
            $applicableAmount = $this->getApplicableAmount($products);
            if ($applicableAmount == 0) {
                return 0;
            }
            $amount = $applicableAmount;
        }

        return match ($this->type) {
            'percentage' => $amount * ($this->discount_value / 100),
            'fixed_amount' => min($this->discount_value, $amount),
            default => 0,
        };
    }

    private function getApplicableAmount($products)
    {
        $applicableAmount = 0;

        foreach ($products as $product) {
            $isApplicable = false;

            if ($this->applicable_products && in_array($product['id'], $this->applicable_products)) {
                $isApplicable = true;
            }

            if ($this->applicable_categories && in_array($product['category_id'], $this->applicable_categories)) {
                $isApplicable = true;
            }

            if ($isApplicable) {
                $applicableAmount += $product['total_price'];
            }
        }

        return $applicableAmount;
    }
}
