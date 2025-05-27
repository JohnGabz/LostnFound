<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        'last_login_at',
        'last_login_ip',
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
            'last_login_at' => 'datetime',
        ];
    }

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
        
        Log::info('Two-factor authentication enabled', ['user_id' => $this->user_id]);
    }

    /**
     * Disable two-factor authentication for this user.
     */
    public function disableTwoFactorAuthentication(): void
    {
        $this->update(['two_factor_enabled' => false]);

        // Delete all unused OTPs
        $deletedCount = $this->otps()->where('is_used', false)->delete();
        
        Log::info('Two-factor authentication disabled', [
            'user_id' => $this->user_id,
            'deleted_otps' => $deletedCount
        ]);
    }

    /**
     * Generate and send OTP for login.
     */
    public function sendLoginOtp(): UserOtp
    {
        try {
            // Delete any existing unused login OTPs
            $this->otps()
                ->where('purpose', 'login')
                ->where('is_used', false)
                ->delete();

            $otp = UserOtp::createForUser($this, 'login', 5); // 5 minutes expiration
            
            // Send email notification
            $this->notify(new \App\Notifications\LoginOtpNotification($otp->otp_code));
            
            Log::info('Login OTP sent', [
                'user_id' => $this->user_id,
                'otp_id' => $otp->id,
                'expires_at' => $otp->expires_at
            ]);
            
            return $otp;
        } catch (\Exception $e) {
            Log::error('Failed to send login OTP', [
                'user_id' => $this->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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
        if (!$this->locked_until) {
            return false;
        }

        $isLocked = $this->locked_until->isFuture();
        
        // Auto-unlock if lock period has expired
        if (!$isLocked && $this->locked_until->isPast()) {
            $this->unlockAccount();
        }
        
        return $isLocked;
    }

    /**
     * Get the time remaining until unlock (in minutes).
     */
    public function getLockoutTimeRemaining(): ?int
    {
        if (!$this->isLocked()) {
            return null;
        }

        return max(1, $this->locked_until->diffInMinutes(now()));
    }

    /**
     * Record a failed login attempt.
     */
    public function recordFailedLogin(string $reason = 'Invalid credentials'): void
    {
        $currentAttempts = $this->failed_login_attempts;
        
        $this->increment('failed_login_attempts');
        $this->update(['last_failed_login' => now()]);

        Log::info('Failed login recorded', [
            'user_id' => $this->user_id,
            'previous_attempts' => $currentAttempts,
            'new_attempts' => $this->failed_login_attempts,
            'reason' => $reason
        ]);

        // Lock account after 5 failed attempts
        if ($this->failed_login_attempts >= 5) {
            $this->lockAccount();
        }

        // Log the attempt
        try {
            LoginAttempt::logAttempt(
                $this->email,
                request()->ip(),
                request()->userAgent(),
                false,
                $reason
            );
        } catch (\Exception $e) {
            Log::error('Failed to log login attempt', [
                'user_id' => $this->user_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Record a successful login.
     */
    public function recordSuccessfulLogin(): void
    {
        $previousAttempts = $this->failed_login_attempts;
        
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_failed_login' => null,
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        Log::info('Successful login recorded', [
            'user_id' => $this->user_id,
            'cleared_attempts' => $previousAttempts,
            'login_ip' => request()->ip()
        ]);

        // Log the successful attempt
        try {
            LoginAttempt::logAttempt(
                $this->email,
                request()->ip(),
                request()->userAgent(),
                true
            );
        } catch (\Exception $e) {
            Log::error('Failed to log successful login attempt', [
                'user_id' => $this->user_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Lock the account for a specified duration.
     */
    public function lockAccount(int $minutes = 30): void
    {
        $lockedUntil = now()->addMinutes($minutes);
        
        $this->update([
            'locked_until' => $lockedUntil,
        ]);

        Log::warning('Account locked', [
            'user_id' => $this->user_id,
            'locked_until' => $lockedUntil,
            'duration_minutes' => $minutes,
            'failed_attempts' => $this->failed_login_attempts
        ]);
    }

    /**
     * Unlock the account manually.
     */
    public function unlockAccount(): void
    {
        $wasLocked = $this->isLocked();
        
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

    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class, 'user_id', 'user_id');
    }

}
