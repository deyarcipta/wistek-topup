<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'max_uses',
        'used_count',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'value' => 'float',
        'min_purchase' => 'float',
        'max_discount' => 'float',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'valid_until' => 'datetime',
    ];

    /**
     * Check if the voucher is valid for use
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        if ($this->max_uses > 0 && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Check if the purchase amount meets the minimum purchase requirement
     */
    public function meetsMinPurchase(float $amount): bool
    {
        if ($this->min_purchase === null || $this->min_purchase <= 0) {
            return true;
        }

        return $amount >= (float) $this->min_purchase;
    }

    /**
     * Calculate discount amount based on original price
     */
    public function calculateDiscount(float $originalPrice): float
    {
        if (! $this->meetsMinPurchase($originalPrice)) {
            return 0.0;
        }

        $discount = 0.0;

        if ($this->type === 'percent') {
            $discount = ($originalPrice * $this->value) / 100;
            if ($this->max_discount !== null && $this->max_discount > 0) {
                $discount = min($discount, (float) $this->max_discount);
            }
        } else {
            $discount = $this->value;
        }

        // Limit discount to the original price to prevent negative prices
        return max(0.0, min($discount, $originalPrice));
    }
}
