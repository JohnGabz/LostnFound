<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

class VerificationController extends Controller
{
    /**
     * Show the email verification notice.
     */
    public function show()
    {
        return Auth::user()->hasVerifiedEmail()
            ? redirect('dashboard')
            : view('Authentication.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified.
     * Updated to handle custom verification logic if needed
     */
    public function verify(EmailVerificationRequest $request)
    {
        // Check if already verified
        if ($request->user()->hasVerifiedEmail()) {
            Log::info('Email verification attempted but already verified', [
                'user_id' => $request->user()->user_id,
                'email' => $request->user()->email
            ]);
            return redirect('dashboard')->with('status', 'Your email is already verified!');
        }

        // Mark as verified
        $request->fulfill();

        // Log successful verification
        Log::info('Email verified successfully', [
            'user_id' => $request->user()->user_id,
            'email' => $request->user()->email,
            'email_verified_at' => $request->user()->fresh()->email_verified_at
        ]);

        return redirect('dashboard')->with('status', 'Your email has been verified!');
    }

    /**
     * Alternative verification method for manual verification
     * This bypasses Laravel's built-in signature verification
     */
    public function verifyManual(Request $request, $id, $hash)
    {
        // Find the user
        $user = User::findOrFail($id);
        
        // Verify the hash matches the user's email
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            Log::warning('Email verification failed - hash mismatch', [
                'user_id' => $id,
                'provided_hash' => $hash,
                'expected_hash' => sha1($user->getEmailForVerification())
            ]);
            
            return redirect()->route('verification.notice')
                ->withErrors(['email' => 'Invalid verification link.']);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            Log::info('Manual email verification attempted but already verified', [
                'user_id' => $user->user_id,
                'email' => $user->email
            ]);
            
            return redirect('dashboard')->with('status', 'Your email is already verified!');
        }

        // Mark as verified
        $user->markEmailAsVerified();
        
        // Fire the verified event
        event(new Verified($user));

        Log::info('Email verified manually', [
            'user_id' => $user->user_id,
            'email' => $user->email,
            'verification_method' => 'manual'
        ]);

        // Log the user in if not already authenticated
        if (!Auth::check()) {
            Auth::login($user);
        }

        return redirect('dashboard')->with('status', 'Your email has been verified!');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('dashboard');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            
            Log::info('Email verification notification resent', [
                'user_id' => $request->user()->user_id,
                'email' => $request->user()->email
            ]);

            return back()->with('status', 'A fresh verification link has been sent to your email address.');
            
        } catch (\Exception $e) {
            Log::error('Failed to resend email verification', [
                'user_id' => $request->user()->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['email' => 'Failed to send verification email. Please try again.']);
        }
    }
}