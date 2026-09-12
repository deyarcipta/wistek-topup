<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'title',
        'discount_price',
        'stock_total',
        'stock_sold',
        'start_at',
        'end_at',
        'is_active',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'discount_price' => 'decimal:2',
        'stock_total' => 'integer',
        'stock_sold' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if flash sale is currently active and within start/end time
     */
    public function isRunning(): bool
    {
        return $this->is_active &&
            $this->start_at &&
            $this->end_at &&
            now()->between($this->start_at, $this->end_at);
    }

    /**
     * Calculate discount percentage relative to original product price
     */
    public function getDiscountPercentageAttribute(): int
    {
        if (! $this->product || $this->product->price_sell <= 0) {
            return 0;
        }

        $originalPrice = (float) $this->product->price_sell;
        $discountPrice = (float) $this->discount_price;

        if ($discountPrice >= $originalPrice) {
            return 0;
        }

        return (int) round((($originalPrice - $discountPrice) / $originalPrice) * 100);
    }

    /**
     * Get stock remaining
     */
    public function getStockRemainingAttribute(): int
    {
        return max(0, $this->stock_total - $this->stock_sold);
    }
}
