<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function index()
    {
        // Sample claims for testing
        $pendingClaims = collect([
            (object) [
                'id' => 1,
                'status' => 'pending',
                'message' => 'This is my ID card. I lost it yesterday.',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
                'item' => (object) [
                    'id' => 4,
                    'title' => 'Student ID Card',
                    'type' => 'found',
                    'image_path' => null,
                    'user' => (object) ['name' => 'Jane Doe']
                ]
            ]
        ]);
        
        $approvedClaims = collect([
            (object) [
                'id' => 2,
                'status' => 'approved',
                'message' => 'This is my MacBook charger that I lost last week.',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(2),
                'item' => (object) [
                    'id' => 3,
                    'title' => 'MacBook Charger',
                    'type' => 'found',
                    'image_path' => null,
                    'user' => (object) ['name' => 'John Doe', 'email' => 'john@example.com']
                ]
            ]
        ]);
        
        $rejectedClaims = collect([
            (object) [
                'id' => 3,
                'status' => 'rejected',
                'message' => 'I think this is my wallet that I lost.',
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(5),
                'item' => (object) [
                    'id' => 5,
                    'title' => 'Black Wallet',
                    'type' => 'found',
                    'image_path' => null,
                    'user' => (object) ['name' => 'Alice Johnson']
                ]
            ]
        ]);
        
        return view('claim-items', compact('pendingClaims', 'approvedClaims', 'rejectedClaims'));
    }
    
    public function store(Request $request)
    {
        // Dummy method for testing
        return redirect()->back()->with('success', 'Claim submitted successfully!');
    }
    
    public function update(Request $request, $id)
    {
        // Dummy method for testing
        return redirect()->back()->with('success', 'Claim status updated!');
    }
}