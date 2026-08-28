<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'used_count',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'value' => 'float',
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
     * Calculate discount amount based on original price
     */
    public function calculateDiscount(float $originalPrice): float
    {
        $discount = 0.0;

        if ($this->type === 'percent') {
            $discount = ($originalPrice * $this->value) / 100;
        } else {
            $discount = $this->value;
        }

        // Limit discount to the original price to prevent negative prices
        return min($discount, $originalPrice);
    }
}
