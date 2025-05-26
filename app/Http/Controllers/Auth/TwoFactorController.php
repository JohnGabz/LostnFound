<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
    /**
     * Show the two factor authentication setup form.
     */
    public function show()
    {
        $user = Auth::user();
        
        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $this->generateSecretKey();
            $user->save();
        }

        $qrCodeUrl = $this->getQrCodeUrl($user);

        return view('Authentication.two-factor', [
            'secret' => $user->two_factor_secret,
            'qrCodeUrl' => $qrCodeUrl,
            'recoveryCodes' => $user->two_factor_recovery_codes
        ]);
    }

    /**
     * Enable two factor authentication.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        
        if (!$this->verifyCode($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'The provided two factor authentication code is invalid.']);
        }

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $user->generateRecoveryCodes(),
        ]);

        return redirect()->route('two-factor.show')->with('status', 'Two factor authentication has been enabled.');
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

        Auth::user()->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('two-factor.show')->with('status', 'Two factor authentication has been disabled.');
    }

    /**
     * Generate new recovery codes.
     */
    public function regenerateRecoveryCodes()
    {
        $user = Auth::user();
        
        if (!$user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor.show');
        }

        $user->update([
            'two_factor_recovery_codes' => $user->generateRecoveryCodes(),
        ]);

        return redirect()->route('two-factor.show')->with('status', 'New recovery codes have been generated.');
    }

    /**
     * Show the two factor challenge form.
     */
    public function challenge()
    {
        // Check if there's a user ID stored for 2FA verification
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }
        
        return view('Authentication.two-factor-challenge');
    }

    /**
     * Verify the two factor challenge.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        // Get user from session
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        $code = str_replace(' ', '', $request->code);

        // Check if it's a recovery code
        if (strlen($code) === 10) {
            return $this->verifyRecoveryCode($user, $code);
        }

        // Check if it's a TOTP code
        if (strlen($code) === 6) {
            return $this->verifyTotpCode($user, $code);
        }

        return back()->withErrors(['code' => 'The provided code is invalid.']);
    }

    /**
     * Verify recovery code.
     */
    private function verifyRecoveryCode($user, $code)
    {
        $recoveryCodes = $user->two_factor_recovery_codes;
        
        if (!$recoveryCodes || !in_array($code, $recoveryCodes)) {
            return back()->withErrors(['code' => 'The provided recovery code is invalid.']);
        }

        // Remove used recovery code
        $recoveryCodes = array_values(array_diff($recoveryCodes, [$code]));
        $user->update(['two_factor_recovery_codes' => $recoveryCodes]);

        $this->completeTwoFactorChallenge();
        
        return redirect()->intended('dashboard');
    }

    /**
     * Verify TOTP code.
     */
    private function verifyTotpCode($user, $code)
    {
        if (!$this->verifyCode($user->two_factor_secret, $code)) {
            return back()->withErrors(['code' => 'The provided two factor authentication code is invalid.']);
        }

        $this->completeTwoFactorChallenge();
        
        return redirect()->intended('dashboard');
    }

    /**
     * Complete the two factor challenge.
     */
    private function completeTwoFactorChallenge()
    {
        $userId = session('2fa_user_id');
        $user = \App\Models\User::find($userId);
        
        // Log the user back in
        Auth::login($user);
        
        // Mark 2FA as verified
        session(['2fa_verified' => true, '2fa_verified_at' => now()]);
        
        // Clear the temporary session data
        session()->forget('2fa_user_id');
    }

    /**
     * Generate a secret key for 2FA.
     */
    private function generateSecretKey()
    {
        return Str::random(32);
    }

    /**
     * Generate QR code URL for Google Authenticator.
     */
    private function getQrCodeUrl($user)
    {
        $appName = config('app.name', 'LostnFound');
        $secret = $user->two_factor_secret;
        $email = $user->email;

        $qrCodeUrl = "otpauth://totp/{$appName}:{$email}?secret={$secret}&issuer={$appName}";
        
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrCodeUrl);
    }

    /**
     * Verify TOTP code with time window (5+ minutes validity).
     */
    private function verifyCode($secret, $code)
    {
        $timeStep = 30; // 30 seconds per step
        $currentTime = time();
        
        // Check current time and previous/next time steps (5+ minutes window)
        for ($i = -10; $i <= 10; $i++) {
            $time = $currentTime + ($i * $timeStep);
            $generatedCode = $this->generateTotpCode($secret, $time);
            
            if (hash_equals($generatedCode, $code)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Generate TOTP code for given time.
     */
    private function generateTotpCode($secret, $time = null)
    {
        $time = $time ?: time();
        $time = floor($time / 30);
        
        $data = pack('N*', 0, $time);
        $hash = hash_hmac('sha1', $data, $secret, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $value = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;
        
        return str_pad($value % 1000000, 6, '0', STR_PAD_LEFT);
    }
}