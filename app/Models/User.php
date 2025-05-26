<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // Decrypt two factor secret
    public function getTwoFactorSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    // Encrypt two factor secret
    public function setTwoFactorSecretAttribute($value)
    {
        $this->attributes['two_factor_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    // Decrypt recovery codes
    public function getTwoFactorRecoveryCodesAttribute($value)
    {
        return $value ? json_decode(Crypt::decryptString($value), true) : null;
    }

    // Encrypt recovery codes
    public function setTwoFactorRecoveryCodesAttribute($value)
    {
        $this->attributes['two_factor_recovery_codes'] = $value ? Crypt::encryptString(json_encode($value)) : null;
    }

    // Check if two-factor auth is fully enabled
    public function hasEnabledTwoFactorAuthentication()
    {
        return $this->two_factor_enabled &&
               !is_null($this->two_factor_secret) &&
               !is_null($this->two_factor_confirmed_at);
    }

    // Generate new recovery codes
    public function generateRecoveryCodes()
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5)));
        }
        return $codes;
    }

    // Cast isAdmin as a computed attribute
    protected function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->role === 'admin',
        );
    }
}
