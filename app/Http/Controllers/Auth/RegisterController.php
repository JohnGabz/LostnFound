<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('Authentication.register');
    }

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('User registration validation failed', [
                'ip' => $request->ip(),
                'errors' => $e->errors(),
                'input' => $request->only('name', 'email'),
            ]);
            throw $e; // rethrow validation exception so normal flow handles errors
        }

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
        } catch (\Exception $e) {
            Log::error('User registration failed during user creation', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['email' => 'Registration failed. Please try again later.']);
        }

        event(new Registered($user));

        Auth::login($user);

        Log::info('User registered and logged in', [
            'user_id' => $user->user_id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return redirect('email/verify')->with('status', 'A verification link has been sent to your email address.');
    }
}
