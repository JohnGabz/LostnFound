<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $primaryKey = 'user_id';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'two_factor_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'two_factor_enabled' => 'boolean',
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
}