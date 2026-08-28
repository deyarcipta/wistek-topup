<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointLog extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'amount',
        'type', // earn, spend, referral_bonus, expire
        'description',
        'expired_at',
        'is_expired',
    ];

    protected $casts = [
        'amount' => 'integer',
        'is_expired' => 'boolean',
        'expired_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
