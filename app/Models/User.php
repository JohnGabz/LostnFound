<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
<<<<<<< HEAD
use Illuminate\Support\Facades\Crypt;
=======
use Illuminate\Database\Eloquent\Casts\Attribute;
>>>>>>> c9bc94c54c77c8d0bf73cc27f92cbdd8fc9ba5e0

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
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

<<<<<<< HEAD
    /**
     * Get the user's two factor authentication secret.
     */
    public function getTwoFactorSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set the user's two factor authentication secret.
     */
    public function setTwoFactorSecretAttribute($value)
    {
        $this->attributes['two_factor_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the user's two factor recovery codes.
     */
    public function getTwoFactorRecoveryCodesAttribute($value)
    {
        return $value ? json_decode(Crypt::decryptString($value), true) : null;
    }

    /**
     * Set the user's two factor recovery codes.
     */
    public function setTwoFactorRecoveryCodesAttribute($value)
    {
        $this->attributes['two_factor_recovery_codes'] = $value ? Crypt::encryptString(json_encode($value)) : null;
    }

    /**
     * Determine if two-factor authentication is enabled.
     */
    public function hasEnabledTwoFactorAuthentication()
    {
        return $this->two_factor_enabled && 
               !is_null($this->two_factor_secret) && 
               !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Generate new recovery codes.
     */
    public function generateRecoveryCodes()
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5)));
        }
        return $codes;
    }
}
=======
    public function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->role === 'admin',
        );
    }

}
>>>>>>> c9bc94c54c77c8d0bf73cc27f92cbdd8fc9ba5e0
