<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    /**
     * Show the two factor authentication settings page.
     */
    public function show()
    {
        $user = Auth::user();
        
        return view('Authentication.two-factor', [
            'user' => $user,
            'isEnabled' => $user->hasEnabledTwoFactorAuthentication()
        ]);
    }

    /**
     * Enable two factor authentication.
     */
    public function enable(Request $request)
    {
        $user = Auth::user();
        
        if ($user->hasEnabledTwoFactorAuthentication()) {
            return back()->with('status', 'Two-factor authentication is already enabled.');
        }

        $user->enableTwoFactorAuthentication();

        return back()->with('status', 'Two-factor authentication has been enabled successfully. You will now receive an OTP via email when logging in.');
    }

    /**
     * Disable two factor authentication.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        if (!password_verify($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'The provided password is incorrect.']);
        }

        $user = Auth::user();
        $user->disableTwoFactorAuthentication();

        return back()->with('status', 'Two-factor authentication has been disabled.');
    }

    /**
     * Show the two factor challenge form.
     */
    public function challenge()
    {
        // Check if there's a user ID stored for 2FA verification
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please login again.']);
        }
        
        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'User not found. Please login again.']);
        }

        return view('Authentication.two-factor-challenge', [
            'user' => $user,
            'email' => $user->email
        ]);
    }

    /**
     * Send OTP to user's email.
     */
    public function sendOtp(Request $request)
    {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return response()->json(['error' => 'Session expired. Please login again.'], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found. Please login again.'], 400);
        }

        // Check if user can request a new OTP (rate limiting)
        $lastOtp = $user->otps()
            ->where('type', 'login')
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < 60) {
            return response()->json([
                'error' => 'Please wait before requesting a new code.',
                'wait_time' => 60 - $lastOtp->created_at->diffInSeconds(now())
            ], 429);
        }

        try {
            $otp = $user->sendLoginOtp();
            
            return response()->json([
                'success' => true,
                'message' => 'A new verification code has been sent to your email.',
                'expires_at' => $otp->expires_at->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send OTP. Please try again.'], 500);
        }
    }

    /**
     * Verify the two factor challenge.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        // Get user from session
        $userId = session('2fa_user_id');
        if (!$userId) {
            return back()->withErrors(['otp_code' => 'Session expired. Please login again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return back()->withErrors(['otp_code' => 'User not found. Please login again.']);
        }

        // Verify the OTP code
        if (!UserOtp::verifyCode($user, $request->otp_code, 'login')) {
            return back()->withErrors(['otp_code' => 'Invalid or expired verification code.']);
        }

        // Complete the login process
        Auth::login($user);
        
        // Mark 2FA as verified for this session
        session(['2fa_verified' => true, '2fa_verified_at' => now()]);
        
        // Clear the temporary session data
        session()->forget('2fa_user_id');
        
        return redirect()->intended('dashboard')->with('status', 'Login successful!');
    }

    /**
     * Test sending OTP (for development/testing).
     */
    public function testOtp()
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        $user = Auth::user();
        $otp = $user->sendLoginOtp();
        
        return response()->json([
            'message' => 'Test OTP sent successfully',
            'otp_code' => $otp->otp_code, // Only show in development
            'expires_at' => $otp->expires_at
        ]);
    }
}