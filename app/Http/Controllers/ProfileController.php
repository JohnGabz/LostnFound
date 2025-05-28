<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the profile settings form.
     */
    public function edit()
    {
        return view('profile');
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\-\.\']+$/'
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('users')->ignore($user->user_id, 'user_id')
            ],
            // ADDED: Contact number validation
            'contact_number' => [
                'required',
                'string',
                'regex:/^09\d{9}$/',
                Rule::unique('users')->ignore($user->user_id, 'user_id')
            ],
            // ADDED: Contact visibility setting
            'show_contact_publicly' => ['boolean'],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
        ], [
            'name.regex' => 'Name can only contain letters, spaces, hyphens, dots, and apostrophes.',
            'contact_number.regex' => 'Please enter a valid Philippine mobile number (11 digits starting with 09).',
            'contact_number.unique' => 'This contact number is already registered by another user.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        try {
            // Track what changed for logging
            $changes = [];

            if ($user->name !== $validated['name']) {
                $changes[] = 'name';
            }

            if ($user->email !== $validated['email']) {
                $changes[] = 'email';
            }

            // ADDED: Track contact number changes
            if ($user->contact_number !== $validated['contact_number']) {
                $changes[] = 'contact_number';
            }

            // ADDED: Track contact visibility changes
            $newContactSetting = isset($validated['show_contact_publicly']) && $validated['show_contact_publicly'];
            if ($user->show_contact_publicly !== $newContactSetting) {
                $changes[] = 'contact_visibility';
            }

            // Prepare update data
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'contact_number' => $validated['contact_number'], // ADDED
                'show_contact_publicly' => $newContactSetting, // ADDED
            ];

            // Add password if provided
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
                $changes[] = 'password';
            }

            // Update the user
            $user->update($updateData);

            // Log the update
            Log::info('User profile updated', [
                'user_id' => $user->user_id,
                'changes' => $changes,
                'show_contact_publicly' => $newContactSetting
            ]);

            $message = 'Profile updated successfully!';

            // If email changed, mention verification
            if (in_array('email', $changes) && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
                $user->sendEmailVerificationNotification();
                $message .= ' Please check your new email address for verification.';
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Profile update failed', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update profile. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the password change form.
     */
    public function showPasswordForm()
    {
        return view('profile-password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'different:current_password'
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.different' => 'New password must be different from current password.',
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        try {
            // Update password
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            // Log the password change
            Log::info('User password changed', [
                'user_id' => $user->user_id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->route('profile.edit')
                ->with('success', 'Password updated successfully!');

        } catch (\Exception $e) {
            Log::error('Password update failed', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update password. Please try again.');
        }
    }

    /**
     * Show user's contact preferences and statistics.
     */
    public function contactSettings()
    {
        $user = auth()->user();

        // Get statistics about contact visibility impact
        $itemsPosted = $user->items()->count();
        $itemsWithContact = $user->items()
            ->where('created_at', '>=', $user->updated_at) // Items posted since last profile update
            ->count();

        return view('profile-contact-settings', compact('user', 'itemsPosted', 'itemsWithContact'));
    }

    /**
     * Toggle contact visibility quickly.
     */
    public function toggleContactVisibility(Request $request)
    {
        $user = auth()->user();

        $newSetting = !$user->show_contact_publicly;

        $user->update([
            'show_contact_publicly' => $newSetting
        ]);

        Log::info('Contact visibility toggled', [
            'user_id' => $user->user_id,
            'new_setting' => $newSetting
        ]);

        $message = $newSetting
            ? 'Contact number is now visible to other users on your posts.'
            : 'Contact number is now private and hidden from other users.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'show_contact_publicly' => $newSetting
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}