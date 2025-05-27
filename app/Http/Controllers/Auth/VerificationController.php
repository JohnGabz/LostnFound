<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     */
    public function verify(EmailVerificationRequest $request)
    {
        // Check if already verified
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('dashboard')->with('status', 'Your email is already verified!');
        }

        // Mark as verified
        $request->fulfill();

        // Log for debugging
        \Illuminate\Support\Facades\Log::info('Email verified for user', [
            'user_id' => $request->user()->user_id,
            'email' => $request->user()->email,
            'email_verified_at' => $request->user()->fresh()->email_verified_at
        ]);

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

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A fresh verification link has been sent to your email address.');
    }
}