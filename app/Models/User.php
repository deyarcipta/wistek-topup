<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'username', 'phone', 'role', 'referral_code', 'referred_by_id', 'registration_ip', 'points_balance', 'profile_photo_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
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
}
