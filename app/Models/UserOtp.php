<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOtp extends Model
{
    protected $fillable = [
        'user_id',
        'otp_code',
        'type',
        'expires_at',
        'is_used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Get the user that owns the OTP.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Check if the OTP is valid (not expired and not used).
     */
    public function isValid(): bool
    {
        return !$this->is_used && $this->expires_at->isFuture();
    }

    /**
     * Mark the OTP as used.
     */
    public function markAsUsed(): void
    {
        $this->update(['is_used' => true]);
    }

    /**
     * Generate a random 6-digit OTP code.
     */
    public static function generateCode(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new OTP for a user.
     */
    public static function createForUser(User $user, string $type = 'login', int $expiresInMinutes = 5): self
    {
        // Delete any existing unused OTPs for this user and type
        self::where('user_id', $user->user_id)
            ->where('type', $type)
            ->where('is_used', false)
            ->delete();

        return self::create([
            'user_id' => $user->user_id,
            'otp_code' => self::generateCode(),
            'type' => $type,
            'expires_at' => now()->addMinutes($expiresInMinutes),
        ]);
    }

    /**
     * Verify an OTP code for a user.
     */
    public static function verifyCode(User $user, string $code, string $type = 'login'): bool
    {
        $otp = self::where('user_id', $user->user_id)
            ->where('otp_code', $code)
            ->where('type', $type)
            ->where('is_used', false)
            ->first();

        if (!$otp || !$otp->isValid()) {
            return false;
        }

        $otp->markAsUsed();
        return true;
    }

    /**
     * Create a password reset OTP for a user.
     */
    public static function createPasswordResetOtp(User $user, int $expiresInMinutes = 10): self
    {
        // Delete any existing unused password reset OTPs for this user
        self::where('user_id', $user->user_id)
            ->where('type', 'password_reset')
            ->where('is_used', false)
            ->delete();

        return self::create([
            'user_id' => $user->user_id,
            'otp_code' => self::generateCode(),
            'type' => 'password_reset',
            'expires_at' => now()->addMinutes($expiresInMinutes),
        ]);
    }

    /**
     * Verify password reset OTP and return the user.
     */
    public static function verifyPasswordResetOtp(string $email, string $code): ?User
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return null;
        }

        $otp = self::where('user_id', $user->user_id)
            ->where('otp_code', $code)
            ->where('type', 'password_reset')
            ->where('is_used', false)
            ->first();

        if (!$otp || !$otp->isValid()) {
            return null;
        }

        $otp->markAsUsed();
        return $user;
    }
}