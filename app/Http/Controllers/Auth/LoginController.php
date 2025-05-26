<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            
            // Check if user's email is verified first
            if (!$user->hasVerifiedEmail()) {
                return redirect('email/verify');
            }
            
            // Check if user has 2FA enabled
            if ($user->hasEnabledTwoFactorAuthentication()) {
                // Store user ID in session for 2FA verification
                session(['2fa_user_id' => $user->user_id]);
                
                // Send OTP to user's email
                try {
                    $user->sendLoginOtp();
                    $message = 'A verification code has been sent to your email address.';
                } catch (\Exception $e) {
                    $message = 'Please check your email for the verification code.';
                }
                
                // Logout user temporarily until 2FA is verified
                Auth::logout();
                
                return redirect()->route('two-factor.challenge')->with('status', $message);
            }
            
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