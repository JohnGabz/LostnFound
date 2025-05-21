<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Sample data for testing
        $totalPosts = 50;
        $lostCount = 30;
        $foundCount = 20;
        $claimedCount = 10;
        
        $lostPercentage = ($lostCount / $totalPosts) * 100;
        $foundPercentage = ($foundCount / $totalPosts) * 100;
        $claimedPercentage = ($claimedCount / $totalPosts) * 100;
        
        return view('dashboard', compact(
            'totalPosts', 
            'lostCount', 
            'foundCount', 
            'claimedCount',
            'lostPercentage', 
            'foundPercentage', 
            'claimedPercentage'
        ));
    }
}