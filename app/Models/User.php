<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'two_factor_enabled',
        'failed_login_attempts',
        'locked_until',
        'last_failed_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'locked_until' => 'datetime',
            'last_failed_login' => 'datetime',
        ];
    }

    // Decrypt two factor secret
    /**
     * Get the user's OTP codes.
     */
    public function otps(): HasMany
    {
        return $this->hasMany(UserOtp::class, 'user_id', 'user_id');
    }

    /**
     * Check if two-factor authentication is enabled for this user.
     */
    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return $this->two_factor_enabled;
    }

    /**
     * Enable two-factor authentication for this user.
     */
    public function enableTwoFactorAuthentication(): void
    {
        $this->update(['two_factor_enabled' => true]);
    }

    /**
     * Disable two-factor authentication for this user.
     */
    public function disableTwoFactorAuthentication(): void
    {
        $this->update(['two_factor_enabled' => false]);

        // Delete all unused OTPs
        $this->otps()->where('is_used', false)->delete();
    }

    /**
     * Generate and send OTP for login.
     */
    public function sendLoginOtp(): UserOtp
    {
        $otp = UserOtp::createForUser($this, 'login', 5); // 5 minutes expiration

        // Send email notification
        $this->notify(new \App\Notifications\LoginOtpNotification($otp->otp_code));

        return $otp;
    }

    /**
     * Check if the account is currently locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Get the time remaining until unlock (in minutes).
     */
    public function getLockoutTimeRemaining(): ?int
    {
        if (!$this->isLocked()) {
            return null;
        }

        return $this->locked_until->diffInMinutes(now());
    }

    /**
     * Record a failed login attempt.
     */
    public function recordFailedLogin(string $reason = 'Invalid credentials'): void
    {
        $this->increment('failed_login_attempts');
        $this->update(['last_failed_login' => now()]);

        // Lock account after 5 failed attempts
        if ($this->failed_login_attempts >= 5) {
            $this->lockAccount();
        }

        // Log the attempt
        LoginAttempt::logAttempt(
            $this->email,
            request()->ip(),
            request()->userAgent(),
            false,
            $reason
        );
    }

    /**
     * Record a successful login.
     */
    public function recordSuccessfulLogin(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_failed_login' => null,
        ]);

        // Log the successful attempt
        LoginAttempt::logAttempt(
            $this->email,
            request()->ip(),
            request()->userAgent(),
            true
        );
    }

    /**
     * Lock the account for a specified duration.
     */
    public function lockAccount(int $minutes = 30): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * Unlock the account manually.
     */
    public function unlockAccount(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_failed_login' => null,
        ]);
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }
}
