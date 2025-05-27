<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'successful',
        'failure_reason',
    ];

    protected $casts = [
        'successful' => 'boolean',
    ];

    /**
     * Log a login attempt.
     */
    public static function logAttempt(
        string $email,
        string $ipAddress,
        ?string $userAgent = null,
        bool $successful = false,
        ?string $failureReason = null
    ): self {
        return self::create([
            'email' => $email,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'successful' => $successful,
            'failure_reason' => $failureReason,
        ]);
    }

    /**
     * Get recent failed attempts for an email.
     */
    public static function getRecentFailedAttempts(string $email, int $minutes = 15): int
    {
        return self::where('email', $email)
            ->where('successful', false)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Get recent failed attempts for an IP address.
     */
    public static function getRecentFailedAttemptsFromIp(string $ipAddress, int $minutes = 15): int
    {
        return self::where('ip_address', $ipAddress)
            ->where('successful', false)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Clean up old login attempts.
     */
    public static function cleanup(int $daysToKeep = 30): int
    {
        return self::where('created_at', '<', now()->subDays($daysToKeep))->delete();
    }
}