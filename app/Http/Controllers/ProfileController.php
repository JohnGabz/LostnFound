<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{   
    public function edit()
    {
        return view('profile.edit', compact('user'));
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
}
