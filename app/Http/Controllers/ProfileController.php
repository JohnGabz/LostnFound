<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\User;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show profile edit form
     */
    public function edit()
    {
        $user = auth()->user();

        $this->logAction('Viewed profile edit page');

        return view('profile', compact('user'));
    }

    /**
     * Show claimer profile
     */
    public function claimer($id)
    {
        $user = User::findOrFail($id);

        $this->logAction('Viewed claimer profile', "Claimer ID: {$id}");

        return view('components.claimer', compact('user'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->user_id, 'user_id'),
            ],
        ]);

        $user->update($request->only('name', 'email'));

        $this->logAction('Updated profile', 'User updated their name or email');

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    /**
     * Log user actions
     */
    private function logAction(string $action, ?string $details = null): void
    {
        Log::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
        ]);
    }
}