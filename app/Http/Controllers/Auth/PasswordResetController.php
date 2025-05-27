<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOtp;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

class PasswordResetController extends Controller
{
    public function showForgotForm()
    {
        return view('Authentication.forgot-password');
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::warning("Password reset requested for non-existent email", ['email' => $request->email]);
            return back()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        $lastOtp = $user->otps()
            ->where('type', 'password_reset')
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < 60) {
            $waitTime = 60 - $lastOtp->created_at->diffInSeconds(now());
            Log::info("Password reset OTP requested too soon", ['user_id' => $user->user_id, 'wait_time' => $waitTime]);
            return back()->withErrors(['email' => "Please wait {$waitTime} seconds before requesting another reset code."]);
        }

        try {
            $otp = UserOtp::createPasswordResetOtp($user, 10); // 10 minutes expiry
            $user->notify(new PasswordResetOtpNotification($otp->otp_code));

            Log::info("Password reset OTP sent", ['user_id' => $user->user_id, 'email' => $user->email, 'otp_id' => $otp->id]);

            return redirect()->route('password.verify-otp')
                ->with('status', 'We have sent a password reset code to your email address.')
                ->with('email', $request->email);
        } catch (\Exception $e) {
            Log::error("Failed to send password reset OTP", ['email' => $request->email, 'error' => $e->getMessage()]);
            return back()->withErrors(['email' => 'Unable to send reset code. Please try again later.']);
        }
    }

    public function showVerifyOtpForm(Request $request)
    {
        $email = $request->session()->get('email') ?? $request->get('email');

        if (!$email) {
            Log::warning("Password reset OTP verification form accessed without email in session");
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Session expired. Please request a new reset code.']);
        }

        return view('Authentication.verify-reset-otp', compact('email'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        $user = UserOtp::verifyPasswordResetOtp($request->email, $request->otp_code);

        if (!$user) {
            Log::warning("Invalid or expired password reset OTP attempted", ['email' => $request->email, 'otp_code' => $request->otp_code]);
            return back()->withErrors(['otp_code' => 'Invalid or expired reset code.']);
        }

        Log::info("Password reset OTP verified successfully", ['user_id' => $user->user_id]);

        // Generate token for password reset form
        $token = Password::getRepository()->create($user);

        return redirect()->route('password.reset', ['token' => $token])
            ->with('email', $request->email)
            ->with('status', 'Code verified! Please enter your new password.');
    }

    public function showResetForm(Request $request, string $token)
    {
        $email = $request->get('email') ?? session('email');

        return view('Authentication.reset-password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                // Delete unused password reset OTPs
                $user->otps()->where('type', 'password_reset')->where('is_used', false)->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            Log::info("Password reset successful", ['email' => $request->email]);
            $request->session()->forget('email'); // clear session email after reset
            return redirect()->route('login')
                ->with('status', 'Your password has been reset successfully! Please login with your new password.');
        }

        Log::warning("Password reset failed", ['email' => $request->email, 'status' => $status]);

        return back()->withErrors(['email' => [__($status)]]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::warning("Password reset OTP resend requested for non-existent user", ['email' => $request->email]);
            return response()->json(['error' => 'User not found.'], 404);
        }

        $lastOtp = $user->otps()
            ->where('type', 'password_reset')
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < 60) {
            $waitTime = 60 - $lastOtp->created_at->diffInSeconds(now());
            Log::info("Password reset OTP resend requested too soon", ['user_id' => $user->user_id, 'wait_time' => $waitTime]);
            return response()->json([
                'error' => 'Please wait before requesting another code.',
                'wait_time' => $waitTime
            ], 429);
        }

        try {
            $otp = UserOtp::createPasswordResetOtp($user, 10);
            $user->notify(new PasswordResetOtpNotification($otp->otp_code));

            Log::info("Password reset OTP resent", ['user_id' => $user->user_id, 'otp_id' => $otp->id]);

            return response()->json([
                'success' => true,
                'message' => 'A new password reset code has been sent to your email.',
                'expires_at' => $otp->expires_at->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to resend password reset OTP", ['user_id' => $user->user_id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to send reset code. Please try again.'], 500);
        }
    }
}
