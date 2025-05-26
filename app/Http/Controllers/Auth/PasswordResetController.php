<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOtp;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotForm()
    {
        return view('Authentication.forgot-password');
    }

    /**
     * Send password reset OTP to user's email.
     */
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        // Check rate limiting - prevent spam
        $lastOtp = $user->otps()
            ->where('type', 'password_reset')
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < 60) {
            $waitTime = 60 - $lastOtp->created_at->diffInSeconds(now());
            return back()->withErrors(['email' => "Please wait {$waitTime} seconds before requesting another reset code."]);
        }

        try {
            // Create and send OTP
            $otp = UserOtp::createPasswordResetOtp($user, 10); // 10 minutes expiration
            $user->notify(new PasswordResetOtpNotification($otp->otp_code));

            return redirect()->route('password.verify-otp')
                ->with('status', 'We have sent a password reset code to your email address.')
                ->with('email', $request->email);
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Unable to send reset code. Please try again later.']);
        }
    }

    /**
     * Show the OTP verification form.
     */
    public function showVerifyOtpForm(Request $request)
    {
        $email = $request->session()->get('email') ?? $request->get('email');
        
        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Session expired. Please request a new reset code.']);
        }

        return view('Authentication.verify-reset-otp', compact('email'));
    }

    /**
     * Verify the OTP and show password reset form.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        $user = UserOtp::verifyPasswordResetOtp($request->email, $request->otp_code);

        if (!$user) {
            return back()->withErrors(['otp_code' => 'Invalid or expired reset code.']);
        }

        // Generate a secure token for the password reset form
        $token = Password::getRepository()->create($user);

        return redirect()->route('password.reset', ['token' => $token])
            ->with('email', $request->email)
            ->with('status', 'Code verified! Please enter your new password.');
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request, string $token)
    {
        $email = $request->get('email') ?? session('email');
        
        return view('Authentication.reset-password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Attempt to reset the password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);

                $user->save();

                // Delete all unused password reset OTPs
                $user->otps()->where('type', 'password_reset')->where('is_used', false)->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Your password has been reset successfully! Please login with your new password.');
        }

        return back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Resend password reset OTP.
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        // Check rate limiting
        $lastOtp = $user->otps()
            ->where('type', 'password_reset')
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < 60) {
            $waitTime = 60 - $lastOtp->created_at->diffInSeconds(now());
            return response()->json([
                'error' => 'Please wait before requesting another code.',
                'wait_time' => $waitTime
            ], 429);
        }

        try {
            $otp = UserOtp::createPasswordResetOtp($user, 10);
            $user->notify(new PasswordResetOtpNotification($otp->otp_code));

            return response()->json([
                'success' => true,
                'message' => 'A new password reset code has been sent to your email.',
                'expires_at' => $otp->expires_at->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send reset code. Please try again.'], 500);
        }
    }
}