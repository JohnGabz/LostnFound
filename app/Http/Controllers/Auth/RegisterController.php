<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Rate limiting for registration attempts
     */
    private const MAX_REGISTRATION_ATTEMPTS = 3;
    private const REGISTRATION_LOCKOUT_MINUTES = 60;

    public function showRegistrationForm(): View
    {
        return view('Authentication.register'); // Updated to match your blade file name
    }

    public function register(Request $request): RedirectResponse
    {
        // Rate limiting for registration
        $throttleKey = 'register:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_REGISTRATION_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Log::warning('Registration rate limit exceeded', [
                'ip' => $request->ip(),
                'seconds_remaining' => $seconds
            ]);
            
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ]);
        }

        Log::info('Registration attempt started', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Validate the registration data
        $validated = $this->validateRegistrationRequest($request);

        try {
            // Check if user already exists (additional check)
            if (User::where('email', $validated['email'])->exists()) {
                Log::warning('Registration attempt with existing email', [
                    'email' => $validated['email'],
                    'ip' => $request->ip()
                ]);
                
                RateLimiter::hit($throttleKey, self::REGISTRATION_LOCKOUT_MINUTES * 60);
                
                throw ValidationException::withMessages([
                    'email' => ['An account with this email address already exists.'],
                ]);
            }

            // Check if contact number already exists
            if (User::where('contact_number', $validated['contact_number'])->exists()) {
                Log::warning('Registration attempt with existing contact number', [
                    'contact_number' => $validated['contact_number'],
                    'ip' => $request->ip()
                ]);
                
                RateLimiter::hit($throttleKey, self::REGISTRATION_LOCKOUT_MINUTES * 60);
                
                throw ValidationException::withMessages([
                    'contact_number' => ['This contact number is already registered.'],
                ]);
            }

            // Create the user
            $user = $this->createUser($validated);
            
            Log::info('User created successfully', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'has_contact' => !empty($user->contact_number),
                'show_contact_publicly' => $user->show_contact_publicly
            ]);

            // Fire the Registered event to send verification email
            try {
                event(new Registered($user));
                Log::info('Registration event fired', ['user_id' => $user->user_id]);
            } catch (\Exception $e) {
                Log::error('Failed to fire registration event', [
                    'user_id' => $user->user_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue with registration even if email fails
            }

            // Log the user in
            Auth::login($user);
            
            // Regenerate session for security
            $request->session()->regenerate();
            
            Log::info('User automatically logged in after registration', [
                'user_id' => $user->user_id
            ]);
            
            return redirect()->route('verification.notice')
                ->with('success', 'Account created successfully! Please verify your email address to continue.')
                ->with('status', 'A verification link has been sent to your email address.');
                
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'email' => $validated['email'],
                'contact_number' => $validated['contact_number'] ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);
            
            RateLimiter::hit($throttleKey, self::REGISTRATION_LOCKOUT_MINUTES * 60);
            
            throw ValidationException::withMessages([
                'email' => ['Registration failed. Please try again later.'],
            ]);
        }
    }

    private function validateRegistrationRequest(Request $request): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\-\.\']+$/',
                function ($attribute, $value, $fail) {
                    // Check for consecutive spaces or special characters
                    if (preg_match('/\s{2,}/', $value) || 
                        preg_match('/[\-\.\']{2,}/', $value) ||
                        str_starts_with($value, ' ') ||
                        str_ends_with($value, ' ')) {
                        $fail('The name format is invalid.');
                    }
                    
                    // Check for profanity or inappropriate content
                    $prohibited = ['admin', 'administrator', 'root', 'system', 'test', 'null', 'undefined'];
                    if (in_array(strtolower(trim($value)), $prohibited)) {
                        $fail('This name is not allowed.');
                    }
                }
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:100',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                function ($attribute, $value, $fail) {
                    // Block disposable email domains
                    $disposableDomains = [
                        '10minutemail.com', 'tempmail.org', 'guerrillamail.com',
                        'mailinator.com', 'yopmail.com', 'temp-mail.org'
                    ];
                    
                    $domain = substr(strrchr($value, "@"), 1);
                    if (in_array(strtolower($domain), $disposableDomains)) {
                        $fail('Disposable email addresses are not allowed.');
                    }
                    
                    // Check for plus addressing abuse
                    if (substr_count($value, '+') > 1) {
                        $fail('Invalid email format.');
                    }
                }
            ],
            // ADDED: Contact number validation
            'contact_number' => [
                'required', 
                'string', 
                'regex:/^09\d{9}$/', // Philippine mobile number format (11 digits starting with 09)
                'unique:users,contact_number',
                function ($attribute, $value, $fail) {
                    // Additional validation for Philippine numbers
                    $cleanNumber = preg_replace('/[^0-9]/', '', $value);
                    
                    // Must be exactly 11 digits
                    if (strlen($cleanNumber) !== 11) {
                        $fail('Contact number must be exactly 11 digits.');
                        return;
                    }
                    
                    // Must start with 09 (Philippine mobile format)
                    if (!preg_match('/^09/', $cleanNumber)) {
                        $fail('Contact number must start with 09 (Philippine mobile format).');
                        return;
                    }
                    
                    // Check for valid network prefixes (Philippine carriers)
                    $validPrefixes = [
                        '0905', '0906', '0915', '0916', '0917', '0926', '0927', '0935', '0936', '0937', '0938', '0939', // Globe
                        '0908', '0918', '0919', '0920', '0921', '0928', '0929', '0939', // Smart
                        '0907', '0909', '0910', '0912', '0930', '0946', '0947', '0948', '0949', '0950', // Sun/TNT
                        '0813', '0817', '0904', '0994' // Others
                    ];
                    
                    $prefix = substr($cleanNumber, 0, 4);
                    if (!in_array($prefix, $validPrefixes)) {
                        $fail('Please enter a valid Philippine mobile number.');
                    }
                }
            ],
            // ADDED: Contact visibility preference
            'show_contact_publicly' => ['boolean'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(3), // Check against known breached passwords
                function ($attribute, $value, $fail) {
                    // Additional password checks
                    if (preg_match('/(.)\1{2,}/', $value)) {
                        $fail('Password cannot contain more than 2 consecutive identical characters.');
                    }
                    
                    // Check for common patterns
                    $commonPatterns = ['123', 'abc', 'qwe', 'password', 'admin'];
                    foreach ($commonPatterns as $pattern) {
                        if (stripos($value, $pattern) !== false) {
                            $fail('Password contains common patterns that are not secure.');
                        }
                    }
                }
            ],
            'password_confirmation' => ['required', 'string'],
            'terms' => ['accepted'], // Add terms acceptance if needed
        ], [
            // Custom error messages
            'name.required' => 'Full name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'name.max' => 'Name cannot exceed 100 characters.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, dots, and apostrophes.',
            
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'An account with this email address already exists.',
            'email.max' => 'Email address cannot exceed 100 characters.',
            'email.regex' => 'Please enter a valid email format.',
            
            // ADDED: Contact number error messages
            'contact_number.required' => 'Contact number is required.',
            'contact_number.regex' => 'Please enter a valid Philippine mobile number (11 digits starting with 09).',
            'contact_number.unique' => 'This contact number is already registered.',
            
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            
            'terms.accepted' => 'You must accept the terms and conditions.',
        ]);
    }

    private function createUser(array $validated): User
    {
        // Sanitize name input
        $name = trim($validated['name']);
        $name = preg_replace('/\s+/', ' ', $name); // Replace multiple spaces with single space
        $name = ucwords(strtolower($name)); // Proper case formatting

        // Format contact number (ensure it's clean)
        $contactNumber = preg_replace('/[^0-9]/', '', $validated['contact_number']);

        return User::create([
            'name' => $name,
            'email' => strtolower(trim($validated['email'])),
            'contact_number' => $contactNumber, // ADDED
            'show_contact_publicly' => isset($validated['show_contact_publicly']) ? true : false, // ADDED
            'password' => Hash::make($validated['password']),
            'role' => 'user', // Default role
            'failed_login_attempts' => 0,
            'two_factor_enabled' => false,
        ]);
    }
}