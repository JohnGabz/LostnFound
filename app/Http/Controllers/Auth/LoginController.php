<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Debug logging
            Log::info('User login attempt', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'has_verified_email' => $user->hasVerifiedEmail(),
                'two_factor_enabled' => $user->two_factor_enabled,
                'two_factor_confirmed_at' => $user->two_factor_confirmed_at,
                'has_enabled_2fa' => $user->hasEnabledTwoFactorAuthentication()
            ]);
            
            // Check if user's email is verified first
            if (!$user->hasVerifiedEmail()) {
                Log::info('Redirecting to email verification');
                return redirect('email/verify');
            }
            
            // Check if user has 2FA enabled
            if ($user->hasEnabledTwoFactorAuthentication()) {
                Log::info('User has 2FA enabled, redirecting to 2FA challenge');
                // Store user ID in session for 2FA verification
                session(['2fa_user_id' => $user->user_id]);
                
                // Logout user temporarily until 2FA is verified
                Auth::logout();
                
                return redirect()->route('two-factor.challenge');
            }
            
            Log::info('User login successful, redirecting to dashboard');
            // If no 2FA, proceed normally
            return redirect()->intended('dashboard');
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