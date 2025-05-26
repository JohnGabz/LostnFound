<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class DashboardController extends Controller
{
    public function index()
    {
        $lostCount = Item::where('type', 'lost')->count();
        $foundCount = Item::where('type', 'found')->count();
        $claimedCount = Item::where('status', 'claimed')->count();
        $totalPosts = $lostCount + $foundCount;

        // Avoid division by zero
        $lostPercentage = $totalPosts > 0 ? round(($lostCount / $totalPosts) * 100) : 0;
        $foundPercentage = $totalPosts > 0 ? round(($foundCount / $totalPosts) * 100) : 0;
        $claimedPercentage = $totalPosts > 0 ? round(($claimedCount / $totalPosts) * 100) : 0;

        return view('dashboard', compact(
            'lostCount',
            'foundCount',
            'claimedCount',
            'totalPosts',
            'lostPercentage',
            'foundPercentage',
            'claimedPercentage'
        ));
    }
}