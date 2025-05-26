<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Notifications\AccountLockedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('Authentication.login');
    }

    public function login(Request $request)
    {
        Log::info('Login attempt started', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check for IP-based rate limiting (prevents brute force from same IP)
        $ipFailedAttempts = LoginAttempt::getRecentFailedAttemptsFromIp($request->ip(), 15);
        Log::info('IP failed attempts check', ['ip' => $request->ip(), 'attempts' => $ipFailedAttempts]);
        
        if ($ipFailedAttempts >= 10) {
            Log::warning('IP rate limit exceeded', ['ip' => $request->ip(), 'attempts' => $ipFailedAttempts]);
            return back()->withErrors([
                'email' => 'Too many failed attempts from this IP address. Please try again in 15 minutes.',
            ])->onlyInput('email');
        }

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();
        Log::info('User lookup', [
            'email' => $credentials['email'],
            'user_found' => $user ? 'yes' : 'no',
            'user_id' => $user ? $user->user_id : null
        ]);

        // Check if account is locked
        if ($user && $user->isLocked()) {
            $timeRemaining = $user->getLockoutTimeRemaining();
            Log::warning('Account is locked', [
                'user_id' => $user->user_id,
                'locked_until' => $user->locked_until,
                'time_remaining' => $timeRemaining
            ]);
            return back()->withErrors([
                'email' => "Account is temporarily locked due to multiple failed login attempts. Please try again in {$timeRemaining} minutes.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            Log::info('Authentication successful', ['user_id' => Auth::user()->user_id]);
            
            $request->session()->regenerate();
            $user = Auth::user();
            
            // Record successful login
            try {
                $user->recordSuccessfulLogin();
                Log::info('Successful login recorded', ['user_id' => $user->user_id]);
            } catch (\Exception $e) {
                Log::error('Failed to record successful login', [
                    'user_id' => $user->user_id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Check if user's email is verified first
            if (!$user->hasVerifiedEmail()) {
                Log::info('User email not verified, redirecting', ['user_id' => $user->user_id]);
                return redirect('email/verify');
            }
            
            // Check if user has 2FA enabled
            if ($user->hasEnabledTwoFactorAuthentication()) {
                Log::info('2FA enabled, sending OTP', ['user_id' => $user->user_id]);
                
                // Store user ID in session for 2FA verification
                session(['2fa_user_id' => $user->user_id]);
                
                // Send OTP to user's email
                try {
                    $user->sendLoginOtp();
                    $message = 'A verification code has been sent to your email address.';
                } catch (\Exception $e) {
                    Log::error('Failed to send login OTP', [
                        'user_id' => $user->user_id,
                        'error' => $e->getMessage()
                    ]);
                    $message = 'Please check your email for the verification code.';
                }
                
                // Logout user temporarily until 2FA is verified
                Auth::logout();
                
                return redirect()->route('two-factor.challenge')->with('status', $message);
            }
            
            Log::info('Login completed successfully', ['user_id' => $user->user_id]);
            return redirect()->intended('dashboard');
        }

        Log::warning('Authentication failed', ['email' => $credentials['email']]);

        // Handle failed login attempt
        if ($user) {
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
                
                // Send account locked notification if just locked
                if ($user->failed_login_attempts >= 5) {
                    Log::warning('Account locked due to failed attempts', [
                        'user_id' => $user->user_id,
                        'failed_attempts' => $user->failed_login_attempts
                    ]);
                    
                    try {
                        $user->notify(new AccountLockedNotification(30, $request->ip()));
                        Log::info('Account locked notification sent', ['user_id' => $user->user_id]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send account locked notification', [
                            'user_id' => $user->user_id,
                            'error' => $e->getMessage()
                        ]);
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
                Log::error('Failed to record failed login attempt', [
                    'user_id' => $user->user_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::info('Recording failed attempt for non-existent user', ['email' => $credentials['email']]);
            
            // Log attempt for non-existent user (prevents user enumeration)
            try {
                $loginAttempt = LoginAttempt::logAttempt(
                    $credentials['email'],
                    $request->ip(),
                    $request->userAgent(),
                    false,
                    'User not found'
                );
                Log::info('Failed attempt logged for non-existent user', [
                    'login_attempt_id' => $loginAttempt->id,
                    'email' => $credentials['email']
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to log attempt for non-existent user', [
                    'email' => $credentials['email'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}