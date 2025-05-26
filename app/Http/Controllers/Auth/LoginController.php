<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Log;  // Add this for DB logs
use App\Notifications\AccountLockedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;

class LoginController extends Controller
{
    // Helper method for database logging
    private function logAction(string $action, ?string $details = null): void
    {
        try {
            Log::create([
                'user_id' => auth()->id() ?? null, // could be null if no user logged in
                'action' => $action,
                'details' => $details,
            ]);
        } catch (\Exception $e) {
            // fallback to file log if DB log fails
            LaravelLog::error('Failed to log action to database', [
                'action' => $action,
                'details' => $details,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function showLoginForm()
    {
        $this->logAction('Viewed login form');

        return view('Authentication.login');
    }

    public function login(Request $request)
    {
        LaravelLog::info('Login attempt started', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        $this->logAction('Login attempt started', "Email: {$request->email}, IP: {$request->ip()}");

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $ipFailedAttempts = LoginAttempt::getRecentFailedAttemptsFromIp($request->ip(), 15);
        LaravelLog::info('IP failed attempts check', ['ip' => $request->ip(), 'attempts' => $ipFailedAttempts]);
        $this->logAction('IP failed attempts check', "IP: {$request->ip()}, Attempts: {$ipFailedAttempts}");

        if ($ipFailedAttempts >= 10) {
            LaravelLog::warning('IP rate limit exceeded', ['ip' => $request->ip(), 'attempts' => $ipFailedAttempts]);
            $this->logAction('IP rate limit exceeded', "IP: {$request->ip()}, Attempts: {$ipFailedAttempts}");
            return back()->withErrors([
                'email' => 'Too many failed attempts from this IP address. Please try again in 15 minutes.',
            ])->onlyInput('email');
        }

        $user = User::where('email', $credentials['email'])->first();
        LaravelLog::info('User lookup', [
            'email' => $credentials['email'],
            'user_found' => $user ? 'yes' : 'no',
            'user_id' => $user ? $user->user_id : null
        ]);
        $this->logAction('User lookup', "Email: {$credentials['email']}, User found: " . ($user ? 'yes' : 'no'));

        if ($user && $user->isLocked()) {
            $timeRemaining = $user->getLockoutTimeRemaining();
            LaravelLog::warning('Account is locked', [
                'user_id' => $user->user_id,
                'locked_until' => $user->locked_until,
                'time_remaining' => $timeRemaining
            ]);
            $this->logAction('Account locked', "User ID: {$user->user_id}, Locked until: {$user->locked_until}, Time remaining: {$timeRemaining} minutes");
            return back()->withErrors([
                'email' => "Account is temporarily locked due to multiple failed login attempts. Please try again in {$timeRemaining} minutes.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            LaravelLog::info('Authentication successful', ['user_id' => Auth::user()->user_id]);
            $this->logAction('Authentication successful', "User ID: " . Auth::user()->user_id);

            $request->session()->regenerate();
            $user = Auth::user();

            try {
                $user->recordSuccessfulLogin();
                LaravelLog::info('Successful login recorded', ['user_id' => $user->user_id]);
                $this->logAction('Successful login recorded', "User ID: {$user->user_id}");
            } catch (\Exception $e) {
                LaravelLog::error('Failed to record successful login', [
                    'user_id' => $user->user_id,
                    'error' => $e->getMessage()
                ]);
                $this->logAction('Failed to record successful login', "User ID: {$user->user_id}, Error: " . $e->getMessage());
            }

            if (!$user->hasVerifiedEmail()) {
                LaravelLog::info('User email not verified, redirecting', ['user_id' => $user->user_id]);
                $this->logAction('User email not verified', "User ID: {$user->user_id}");
                return redirect('email/verify');
            }

            if ($user->hasEnabledTwoFactorAuthentication()) {
                LaravelLog::info('2FA enabled, sending OTP', ['user_id' => $user->user_id]);
                $this->logAction('2FA enabled', "User ID: {$user->user_id}");

                session(['2fa_user_id' => $user->user_id]);

                try {
                    $user->sendLoginOtp();
                    $message = 'A verification code has been sent to your email address.';
                    $this->logAction('Login OTP sent', "User ID: {$user->user_id}");
                } catch (\Exception $e) {
                    LaravelLog::error('Failed to send login OTP', [
                        'user_id' => $user->user_id,
                        'error' => $e->getMessage()
                    ]);
                    $this->logAction('Failed to send login OTP', "User ID: {$user->user_id}, Error: " . $e->getMessage());
                    $message = 'Please check your email for the verification code.';
                }

                Auth::logout();

                return redirect()->route('two-factor.challenge')->with('status', $message);
            }

            LaravelLog::info('Login completed successfully', ['user_id' => $user->user_id]);
            $this->logAction('Login completed', "User ID: {$user->user_id}");
            return redirect()->intended('dashboard');
        }

        LaravelLog::warning('Authentication failed', ['email' => $credentials['email']]);
        $this->logAction('Authentication failed', "Email: {$credentials['email']}");

        if ($user) {
            LaravelLog::info('Recording failed login for existing user', [
                'user_id' => $user->user_id,
                'current_attempts' => $user->failed_login_attempts
            ]);
            $this->logAction('Failed login attempt', "User ID: {$user->user_id}, Attempts: {$user->failed_login_attempts}");

            try {
                $user->recordFailedLogin('Invalid password');
                LaravelLog::info('Failed login recorded', [
                    'user_id' => $user->user_id,
                    'new_attempts_count' => $user->fresh()->failed_login_attempts
                ]);
                $this->logAction('Failed login recorded', "User ID: {$user->user_id}, New attempts count: " . $user->fresh()->failed_login_attempts);

                $user = $user->fresh();

                if ($user->failed_login_attempts >= 5) {
                    LaravelLog::warning('Account locked due to failed attempts', [
                        'user_id' => $user->user_id,
                        'failed_attempts' => $user->failed_login_attempts
                    ]);
                    $this->logAction('Account locked', "User ID: {$user->user_id}, Failed attempts: {$user->failed_login_attempts}");

                    try {
                        $user->notify(new AccountLockedNotification(30, $request->ip()));
                        LaravelLog::info('Account locked notification sent', ['user_id' => $user->user_id]);
                        $this->logAction('Account locked notification sent', "User ID: {$user->user_id}");
                    } catch (\Exception $e) {
                        LaravelLog::error('Failed to send account locked notification', [
                            'user_id' => $user->user_id,
                            'error' => $e->getMessage()
                        ]);
                        $this->logAction('Failed to send account locked notification', "User ID: {$user->user_id}, Error: " . $e->getMessage());
                    }

                    return back()->withErrors([
                        'email' => 'Account has been locked due to multiple failed login attempts. Please check your email for details.',
                    ])->onlyInput('email');
                }

                $remainingAttempts = 5 - $user->failed_login_attempts;
                return back()->withErrors([
                    'email' => "Invalid credentials. You have {$remainingAttempts} attempt(s) remaining before your account is locked.",
                ])->onlyInput('email');
            } catch (\Exception $e) {
                LaravelLog::error('Failed to record failed login attempt', [
                    'user_id' => $user->user_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->logAction('Failed to record failed login attempt', "User ID: {$user->user_id}, Error: " . $e->getMessage());
            }
        } else {
            LaravelLog::info('Recording failed attempt for non-existent user', ['email' => $credentials['email']]);
            $this->logAction('Failed login attempt for non-existent user', "Email: {$credentials['email']}");

            try {
                $loginAttempt = LoginAttempt::logAttempt(
                    $credentials['email'],
                    $request->ip(),
                    $request->userAgent(),
                    false,
                    'User not found'
                );
                LaravelLog::info('Failed attempt logged for non-existent user', [
                    'login_attempt_id' => $loginAttempt->id,
                    'email' => $credentials['email']
                ]);
                $this->logAction('Failed attempt logged for non-existent user', "Email: {$credentials['email']}");
            } catch (\Exception $e) {
                LaravelLog::error('Failed to log attempt for non-existent user', [
                    'email' => $credentials['email'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->logAction('Failed to log attempt for non-existent user', "Email: {$credentials['email']}, Error: " . $e->getMessage());
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $this->logAction('User logged out', 'User ID: ' . (auth()->id() ?? 'guest'));

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
