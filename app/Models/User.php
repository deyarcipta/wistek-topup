<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'username', 'phone', 'role', 'tier_level', 'referral_code', 'referred_by_id', 'registration_ip', 'points_balance', 'profile_photo_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                // Generate a unique 8-character referral code prefixed with WSTK-
                $user->referral_code = 'WSTK-'.Str::upper(Str::random(6));
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'points_balance' => 'integer',
        ];
    }

    public function getTierNameAttribute(): string
    {
        return match ($this->tier_level) {
            'platinum' => 'Platinum Member (VVIP)',
            'gold' => 'Gold Member (VIP)',
            default => 'Regular Member',
        };
    }

    /**
     * Check total lifetime spending from paid+success transactions and auto upgrade tier level
     */
    public function checkAndUpgradeTier(): void
    {
        if ($this->isAdmin() || $this->isCashier()) {
            return;
        }

        $totalSpent = (float) Transaction::where('user_id', $this->id)
            ->where('payment_status', 'paid')
            ->where('topup_status', 'success')
            ->sum('price');

        $goldMinSpend = (float) Setting::get('tier_gold_min_spend', '1000000');
        $platinumMinSpend = (float) Setting::get('tier_platinum_min_spend', '5000000');

        $targetTier = 'regular';
        if ($totalSpent >= $platinumMinSpend) {
            $targetTier = 'platinum';
        } elseif ($totalSpent >= $goldMinSpend) {
            $targetTier = 'gold';
        }

        if ($this->tier_level !== $targetTier) {
            $this->tier_level = $targetTier;
            $this->saveQuietly();

            // Trigger WhatsApp notification for tier level upgrade
            if (! empty($this->phone)) {
                $userName = $this->name;
                $userPhone = $this->phone;
                $newTierName = $this->tier_name;

                dispatch(function () use ($userName, $userPhone, $newTierName) {
                    try {
                        $whatsapp = new WhatsappService;
                        $msg = "🎉 *SELAMAT! AKUN WISTEK TOPUP ANDA NAIK LEVEL!* 🎉\n\n"
                             ."Halo {$userName},\n"
                             ."Terima kasih telah setia berbelanja di Wistek Topup! Akun Anda telah resmi naik ke level *{$newTierName}*.\n\n"
                             ."Nikmati harga promo spesial VIP di setiap transaksi Anda!\n\n"
                             ."Terima kasih,\nWistek Topup";

                        $whatsapp->sendMessage($userPhone, $msg);
                    } catch (\Throwable $e) {
                        logger()->error('Tier upgrade WhatsApp alert failed: '.$e->getMessage());
                    }
                })->afterResponse();
            }
        }
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function pointLogs(): HasMany
    {
        return $this->hasMany(PointLog::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    /**
     * Increment user points and log the mutation
     */
    public function incrementPoints(int $amount, string $type, string $description, ?int $transactionId = null, $expiredAt = null): void
    {
        $this->increment('points_balance', $amount);

        $this->pointLogs()->create([
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'expired_at' => $expiredAt,
        ]);
    }

    /**
     * Decrement user points and log the mutation
     */
    public function decrementPoints(int $amount, string $description, ?int $transactionId = null): void
    {
        $this->decrement('points_balance', $amount);

        $this->pointLogs()->create([
            'transaction_id' => $transactionId,
            'amount' => -$amount,
            'type' => 'spend',
            'description' => $description,
        ]);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() || $this->isCashier();
    }
}
