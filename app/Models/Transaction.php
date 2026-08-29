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

    protected static function booted(): void
    {
        static::saved(function (Transaction $transaction) {
            if ($transaction->payment_status === 'paid' && $transaction->topup_status === 'success') {
                $transaction->creditPointsIfEligible();
            }
        });
    }

    /**
     * Credit loyalty points to the member and their referrer if transaction is paid and topup succeeded
     */
    public function creditPointsIfEligible(): bool
    {
        // Syarat & Ketentuan: Pembayaran harus PAID dan status topup harus SUCCESS
        if ($this->payment_status !== 'paid' || $this->topup_status !== 'success') {
            return false;
        }

        $userId = $this->user_id;

        // Auto-detect member from customer_phone if user_id was not explicitly linked
        if (! $userId && ! empty($this->customer_phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $this->customer_phone);
            $basePhone = ltrim($cleanPhone, '0');
            if (str_starts_with($basePhone, '62')) {
                $basePhone = substr($basePhone, 2);
            }
            $variants = ['0'.$basePhone, '62'.$basePhone, $basePhone];
            $foundUser = User::whereIn('phone', $variants)->first();
            if ($foundUser) {
                $userId = $foundUser->id;
                $this->user_id = $userId;
                $this->saveQuietly();
            }
        }

        if (! $userId) {
            return false;
        }

        $user = User::find($userId);
        if (! $user) {
            return false;
        }

        // Pastikan points_earned terhitung (1% dari harga jika belum terisi)
        $pointsToEarn = $this->points_earned;
        if ($pointsToEarn <= 0 && $this->price > 0) {
            $pointsToEarn = (int) ($this->price * 0.01);
            $this->points_earned = $pointsToEarn;
            $this->saveQuietly();
        }

        if ($pointsToEarn <= 0) {
            return false;
        }

        // Cek apakah transaksi ini sudah pernah mendapatkan poin agar tidak double reward
        $alreadyCredited = PointLog::where('user_id', $user->id)
            ->where('transaction_id', $this->id)
            ->where('type', 'earn')
            ->exists();

        if ($alreadyCredited) {
            return false;
        }

        // Tambahkan poin ke member (berlaku 6 bulan)
        $user->incrementPoints(
            $pointsToEarn,
            'earn',
            "Poin dari transaksi {$this->invoice}",
            $this->id,
            now()->addMonths(6)
        );

        // Referral bonus untuk transaksi pertama member yang sukses
        if ($user->referred_by_id) {
            $referrer = User::find($user->referred_by_id);
            if ($referrer) {
                $alreadyRewarded = PointLog::where('user_id', $referrer->id)
                    ->where('type', 'referral_bonus')
                    ->whereHas('transaction', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->exists();

                if (! $alreadyRewarded) {
                    $referrer->incrementPoints(
                        1000,
                        'referral_bonus',
                        "Bonus referral dari member baru: @{$user->username}",
                        $this->id,
                        now()->addMonths(6)
                    );
                }
            }
        }

        return true;
    }
}
