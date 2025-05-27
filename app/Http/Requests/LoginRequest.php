<?php

// 1. Create a new Request class for login validation
// app/Http/Requests/LoginRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [
                'required',
                'string',
                'min:1',
                'max:255'
            ],
            'remember' => ['boolean']
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.regex' => 'Please enter a valid email format.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password cannot be empty.',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
        ]);
    }

    protected function passedValidation(): void
    {
        $throttleKey = Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ]);
        }
    }
}

// 2. Create a new Request class for registration validation
// app/Http/Requests/RegisterRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\-\.\']+$/',
                function ($attribute, $value, $fail) {
                    if (preg_match('/\s{2,}/', $value) || 
                        preg_match('/[\-\.\']{2,}/', $value) ||
                        str_starts_with($value, ' ') ||
                        str_ends_with($value, ' ')) {
                        $fail('The name format is invalid.');
                    }
                    
                    $prohibited = ['admin', 'administrator', 'root', 'system', 'test', 'null', 'undefined'];
                    if (in_array(strtolower(trim($value)), $prohibited)) {
                        $fail('This name is not allowed.');
                    }
                }
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:100',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                function ($attribute, $value, $fail) {
                    $disposableDomains = [
                        '10minutemail.com', 'tempmail.org', 'guerrillamail.com',
                        'mailinator.com', 'yopmail.com', 'temp-mail.org'
                    ];
                    
                    $domain = substr(strrchr($value, "@"), 1);
                    if (in_array(strtolower($domain), $disposableDomains)) {
                        $fail('Disposable email addresses are not allowed.');
                    }
                    
                    if (substr_count($value, '+') > 1) {
                        $fail('Invalid email format.');
                    }
                }
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(3),
                function ($attribute, $value, $fail) {
                    if (preg_match('/(.)\1{2,}/', $value)) {
                        $fail('Password cannot contain more than 2 consecutive identical characters.');
                    }
                    
                    $commonPatterns = ['123', 'abc', 'qwe', 'password', 'admin'];
                    foreach ($commonPatterns as $pattern) {
                        if (stripos($value, $pattern) !== false) {
                            $fail('Password contains common patterns that are not secure.');
                        }
                    }
                }
            ],
            'password_confirmation' => ['required', 'string'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Full name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'name.max' => 'Name cannot exceed 100 characters.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, dots, and apostrophes.',
            
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'An account with this email address already exists.',
            'email.max' => 'Email address cannot exceed 100 characters.',
            'email.regex' => 'Please enter a valid email format.',
            
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            
            'terms.accepted' => 'You must accept the terms and conditions.',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
            'name' => $this->sanitizeName($this->name),
        ]);
    }

    protected function passedValidation(): void
    {
        $throttleKey = 'register:' . $this->ip();
        
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ]);
        }
    }

    private function sanitizeName(?string $name): ?string
    {
        if (!$name) return $name;
        
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name);
        return ucwords(strtolower($name));
    }
}

// 3. Create middleware for additional security
// app/Http/Middleware/SecurityHeaders.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Content Security Policy for auth pages
        if ($request->is('login') || $request->is('register')) {
            $response->headers->set('Content-Security-Policy', 
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; " .
                "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; " .
                "img-src 'self' data:; " .
                "font-src 'self'; " .
                "connect-src 'self'; " .
                "form-action 'self'"
            );
        }

        return $response;
    }
}

// 4. Create custom exception handler for authentication
// app/Exceptions/AuthenticationException.php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class AuthenticationException extends Exception
{
    protected $details;

    public function __construct(string $message, array $details = [], int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
    }

    public function report(): void
    {
        Log::warning('Authentication exception occurred', [
            'message' => $this->getMessage(),
            'details' => $this->details,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}

// 5. Create service class for login attempts tracking
// app/Services/LoginAttemptService.php

namespace App\Services;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LoginAttemptService
{
    private const IP_LOCKOUT_MINUTES = 15;
    private const MAX_IP_ATTEMPTS = 10;
    private const CACHE_PREFIX = 'login_attempts:';

    public function recordAttempt(string $email, string $ip, string $userAgent, bool $success, string $reason = null): LoginAttempt
    {
        try {
            $attempt = LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'success' => $success,
                'reason' => $reason,
                'attempted_at' => now(),
            ]);

            // Update cache for faster lookups
            $this->updateAttemptsCache($ip, $email);

            Log::info('Login attempt recorded', [
                'attempt_id' => $attempt->id,
                'email' => $email,
                'ip' => $ip,
                'success' => $success,
                'reason' => $reason
            ]);

            return $attempt;
        } catch (\Exception $e) {
            Log::error('Failed to record login attempt', [
                'email' => $email,
                'ip' => $ip,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getRecentFailedAttemptsFromIp(string $ip, int $minutes = self::IP_LOCKOUT_MINUTES): int
    {
        $cacheKey = self::CACHE_PREFIX . 'ip:' . $ip;
        
        return Cache::remember($cacheKey, 60, function() use ($ip, $minutes) {
            return LoginAttempt::where('ip_address', $ip)
                ->where('success', false)
                ->where('attempted_at', '>=', now()->subMinutes($minutes))
                ->count();
        });
    }

    public function getRecentFailedAttemptsForEmail(string $email, int $minutes = 60): int
    {
        $cacheKey = self::CACHE_PREFIX . 'email:' . md5($email);
        
        return Cache::remember($cacheKey, 60, function() use ($email, $minutes) {
            return LoginAttempt::where('email', $email)
                ->where('success', false)
                ->where('attempted_at', '>=', now()->subMinutes($minutes))
                ->count();
        });
    }

    public function isIpBlocked(string $ip): bool
    {
        return $this->getRecentFailedAttemptsFromIp($ip) >= self::MAX_IP_ATTEMPTS;
    }

    public function clearAttemptsForUser(User $user): void
    {
        try {
            // Clear from database
            LoginAttempt::where('email', $user->email)
                ->where('success', false)
                ->delete();

            // Clear from cache
            $cacheKey = self::CACHE_PREFIX . 'email:' . md5($user->email);
            Cache::forget($cacheKey);

            Log::info('Login attempts cleared for user', ['user_id' => $user->user_id]);
        } catch (\Exception $e) {
            Log::error('Failed to clear login attempts', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function updateAttemptsCache(string $ip, string $email): void
    {
        // Invalidate cache to force fresh count on next request
        Cache::forget(self::CACHE_PREFIX . 'ip:' . $ip);
        Cache::forget(self::CACHE_PREFIX . 'email:' . md5($email));
    }

    public function getSecurityMetrics(): array
    {
        try {
            $last24Hours = now()->subDay();
            
            return [
                'total_attempts_24h' => LoginAttempt::where('attempted_at', '>=', $last24Hours)->count(),
                'failed_attempts_24h' => LoginAttempt::where('attempted_at', '>=', $last24Hours)
                    ->where('success', false)->count(),
                'successful_logins_24h' => LoginAttempt::where('attempted_at', '>=', $last24Hours)
                    ->where('success', true)->count(),
                'unique_ips_24h' => LoginAttempt::where('attempted_at', '>=', $last24Hours)
                    ->distinct('ip_address')->count('ip_address'),
                'blocked_ips' => $this->getBlockedIpsCount(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get security metrics', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function getBlockedIpsCount(): int
    {
        // This is an approximation - in production you might want to track this more precisely
        return LoginAttempt::select('ip_address')
            ->where('attempted_at', '>=', now()->subMinutes(self::IP_LOCKOUT_MINUTES))
            ->where('success', false)
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) >= ?', [self::MAX_IP_ATTEMPTS])
            ->count();
    }
}

// 6. Create notification for suspicious activity
// app/Notifications/SuspiciousActivityNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuspiciousActivityNotification extends Notification
{
    use Queueable;

    private array $activityDetails;

    public function __construct(array $activityDetails)
    {
        $this->activityDetails = $activityDetails;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Suspicious Activity Detected - ' . config('app.name'))
            ->line('We detected suspicious activity on your account.')
            ->line('Details:')
            ->line('- Time: ' . $this->activityDetails['time'])
            ->line('- IP Address: ' . $this->activityDetails['ip'])
            ->line('- Location: ' . ($this->activityDetails['location'] ?? 'Unknown'))
            ->line('- Activity: ' . $this->activityDetails['activity'])
            ->line('If this was you, you can ignore this message. If not, please secure your account immediately.')
            ->action('Secure My Account', url('/profile/security'))
            ->line('For your security, consider enabling two-factor authentication.');
    }
}
