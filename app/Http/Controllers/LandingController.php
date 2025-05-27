<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingController extends Controller
{
    public function index()
    {
        // Get statistics
        $stats = [
            'lost' => Item::where('type', 'lost')->where('status', '!=', 'claimed')->count(),
            'found' => Item::where('type', 'found')->where('status', '!=', 'claimed')->count(),
            'claimed' => Item::where('status', 'claimed')->count(),
        ];

        // Get recent items for each category (limit to recent ones for performance)
        $lostItems = Item::where('type', 'lost')
            ->where('status', '!=', 'claimed')
            ->with('user')
            ->latest()
            ->take(6)
            ->get();

        $foundItems = Item::where('type', 'found')
            ->where('status', '!=', 'claimed')
            ->with('user')
            ->latest()
            ->take(6)
            ->get();

        $claimedItems = Item::where('status', 'claimed')
            ->with('user')
            ->latest()
            ->take(6)
            ->get();

        return view('landing_page.welcome', compact('stats', 'lostItems', 'foundItems', 'claimedItems'));
    }
}