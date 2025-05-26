<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If user doesn't have 2FA enabled, proceed normally
        if (!$user || !$user->hasEnabledTwoFactorAuthentication()) {
            return $next($request);
        }

        // Check if 2FA was recently verified (within 5 minutes for security)
        $twoFactorVerifiedAt = session('2fa_verified_at');
        
        if (!session('2fa_verified') || 
            !$twoFactorVerifiedAt || 
            now()->diffInMinutes($twoFactorVerifiedAt) > 5) {
            
            // Store intended URL and logout user
            session(['url.intended' => $request->url()]);
            session(['2fa_user_id' => $user->user_id]);
            
            Auth::logout();
            
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}