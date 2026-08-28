<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'invoice',
        'reference',
        'category_name',
        'product_name',
        'sku',
        'target_no',
        'customer_phone',
        'price',
        'voucher_code',
        'discount_amount',
        'points_used',
        'points_earned',
        'payment_method',
        'payment_status',
        'topup_status',
        'note',
        'payment_details',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'user_id' => 'integer',
        'points_used' => 'integer',
        'points_earned' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
