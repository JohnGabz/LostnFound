<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\User;

class ProfileController extends Controller
{
    // Add the reusable logAction method
    private function logAction(string $action, ?string $details = null): void
    {
        Log::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
        ]);
    }

    public function edit()
    {
        $user = auth()->user();

        // Log viewing profile edit page
        $this->logAction('Viewed profile edit page');

        return view('profile', compact('user'));
    }

    public function claimer($id)
    {
        $user = User::with(['claims.claimer'])->findOrFail($id);

        $userHasClaimed = false;
        if (auth()->check()) {
            $userHasClaimed = $user->claims()->where('claimer_id', auth()->id())->exists();
        }

        // Log viewing claimer profile
        $this->logAction('Viewed claimer profile', "Claimer ID: {$id}");

        return view('components.claimer', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            // other validations...
        ]);

        $user->update($request->only('name', 'email'));

        // Log profile update
        $this->logAction('Updated profile', 'User updated their name or email');

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}
