<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Notifications\AccountLockedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Maximum login attempts before rate limiting
     */
    private const MAX_IP_ATTEMPTS = 10;
    private const IP_LOCKOUT_MINUTES = 15;
    private const MAX_EMAIL_ATTEMPTS = 5;
    private const EMAIL_LOCKOUT_MINUTES = 30;

    public function showLoginForm(): View
    {
        return view('Authentication.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // Rate limiting key
        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
        
        // Check rate limiting first
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Log::warning('Rate limit exceeded for login', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'seconds_remaining' => $seconds
            ]);
            
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ]);
        }

        Log::info('Login attempt started', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Validate input with enhanced rules
        $credentials = $this->validateLoginRequest($request);

        // Check IP-based rate limiting
        if ($this->isIpBlocked($request->ip())) {
            Log::warning('IP rate limit exceeded', [
                'ip' => $request->ip(),
                'attempts' => LoginAttempt::getRecentFailedAttemptsFromIp($request->ip(), self::IP_LOCKOUT_MINUTES)
            ]);
            
            throw ValidationException::withMessages([
                'email' => ['Too many failed attempts from this IP address. Please try again in ' . self::IP_LOCKOUT_MINUTES . ' minutes.'],
            ]);
        }

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();
        
        Log::info('User lookup', [
            'email' => $credentials['email'],
            'user_found' => $user ? 'yes' : 'no',
            'user_id' => $user?->user_id
        ]);

        // Check if account is locked
        if ($user && $user->isLocked()) {
            $timeRemaining = $user->getLockoutTimeRemaining();
            Log::warning('Account is locked', [
                'user_id' => $user->user_id,
                'locked_until' => $user->locked_until,
                'time_remaining' => $timeRemaining
            ]);
            
            throw ValidationException::withMessages([
                'email' => ["Account is temporarily locked due to multiple failed login attempts. Please try again in {$timeRemaining} minutes."],
            ]);
        }

        // Attempt authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            return $this->handleSuccessfulLogin($request, Auth::user());
        }

        // Handle failed login
        RateLimiter::hit($throttleKey, 300); // 5 minutes
        return $this->handleFailedLogin($request, $user, $credentials['email']);
    }

    private function validateLoginRequest(Request $request): array
    {
        return $request->validate([
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
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.regex' => 'Please enter a valid email format.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password cannot be empty.',
        ]);
    }

    private function isIpBlocked(string $ip): bool
    {
        $failedAttempts = LoginAttempt::getRecentFailedAttemptsFromIp($ip, self::IP_LOCKOUT_MINUTES);
        return $failedAttempts >= self::MAX_IP_ATTEMPTS;
    }

    private function handleSuccessfulLogin(Request $request, User $user): RedirectResponse
    {
        Log::info('Authentication successful', ['user_id' => $user->user_id]);
        
        // Regenerate session
        $request->session()->regenerate();
        
        try {
            // Record successful login
            $user->recordSuccessfulLogin();
            Log::info('Successful login recorded', ['user_id' => $user->user_id]);
        } catch (\Exception $e) {
            Log::error('Failed to record successful login', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't fail the login for this
        }
        
        // Check email verification
        if (!$user->hasVerifiedEmail()) {
            Log::info('User email not verified, redirecting', ['user_id' => $user->user_id]);
            return redirect()->route('verification.notice')
                ->with('status', 'Please verify your email address to continue.');
        }
        
        // Handle 2FA if enabled
        if ($user->hasEnabledTwoFactorAuthentication()) {
            return $this->handle2FA($user);
        }
        
        Log::info('Login completed successfully', ['user_id' => $user->user_id]);
        return redirect()->intended('dashboard')
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    private function handle2FA(User $user): RedirectResponse
    {
        Log::info('2FA enabled, sending OTP', ['user_id' => $user->user_id]);
        
        // Store user ID in session for 2FA verification
        session(['2fa_user_id' => $user->user_id]);
        
        try {
            // Send OTP to user's email
            $user->sendLoginOtp();
            $message = 'A verification code has been sent to your email address.';
            Log::info('2FA OTP sent successfully', ['user_id' => $user->user_id]);
        } catch (\Exception $e) {
            Log::error('Failed to send login OTP', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $message = 'Please check your email for the verification code.';
        }
        
        // Logout user temporarily until 2FA is verified
        Auth::logout();
        
        return redirect()->route('two-factor.challenge')
            ->with('status', $message);
    }

    private function handleFailedLogin(Request $request, ?User $user, string $email): RedirectResponse
    {
        Log::warning('Authentication failed', ['email' => $email]);

        if ($user) {
            return $this->handleFailedLoginForExistingUser($request, $user);
        } else {
            return $this->handleFailedLoginForNonExistentUser($request, $email);
        }
    }

    private function handleFailedLoginForExistingUser(Request $request, User $user): RedirectResponse
    {
        Log::info('Recording failed login for existing user', [
            'user_id' => $user->user_id,
            'current_attempts' => $user->failed_login_attempts
        ]);
        
        try {
            $user->recordFailedLogin('Invalid password');
            Log::info('Failed login recorded', [
                'user_id' => $user->user_id,
                'new_attempts_count' => $user->fresh()->failed_login_attempts
            ]);
            
            // Refresh user data
            $user = $user->fresh();
            
            // Check if account is now locked
            if ($user->failed_login_attempts >= self::MAX_EMAIL_ATTEMPTS) {
                Log::warning('Account locked due to failed attempts', [
                    'user_id' => $user->user_id,
                    'failed_attempts' => $user->failed_login_attempts
                ]);
                
                $this->sendAccountLockedNotification($user, $request->ip());
                
                throw ValidationException::withMessages([
                    'email' => ['Account has been locked due to multiple failed login attempts. Please check your email for details.'],
                ]);
            }
            
            $remainingAttempts = self::MAX_EMAIL_ATTEMPTS - $user->failed_login_attempts;
            throw ValidationException::withMessages([
                'email' => ["Invalid credentials. You have {$remainingAttempts} attempt(s) remaining before your account is locked."],
            ]);
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to record failed login attempt', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }
    }

    private function handleFailedLoginForNonExistentUser(Request $request, string $email): RedirectResponse
    {
        Log::info('Recording failed attempt for non-existent user', ['email' => $email]);
        
        try {
            $loginAttempt = LoginAttempt::logAttempt(
                $email,
                $request->ip(),
                $request->userAgent(),
                false,
                'User not found'
            );
            
            Log::info('Failed attempt logged for non-existent user', [
                'login_attempt_id' => $loginAttempt->id,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log attempt for non-existent user', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Add artificial delay to prevent user enumeration
        usleep(random_int(100000, 300000)); // 0.1-0.3 seconds

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    private function sendAccountLockedNotification(User $user, string $ip): void
    {
        try {
            $user->notify(new AccountLockedNotification(self::EMAIL_LOCKOUT_MINUTES, $ip));
            Log::info('Account locked notification sent', ['user_id' => $user->user_id]);
        } catch (\Exception $e) {
            Log::error('Failed to send account locked notification', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        Log::info('User logged out', ['user_id' => $userId]);
        
        return redirect('/')->with('success', 'You have been logged out successfully.');
    }
}