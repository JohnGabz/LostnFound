<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('profile', compact('user'));
    }

    public function claimer($id)
    {
        $user = User::with(['claims.claimer'])->findOrFail($id);

        $userHasClaimed = false;
        if (auth()->check()) {
            $userHasClaimed = $user->claims()->where('claimer_id', auth()->id())->exists();
        }

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

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

}
