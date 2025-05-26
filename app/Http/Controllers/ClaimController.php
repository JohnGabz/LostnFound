<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\Item;

class ClaimController extends Controller
{
    public function index()
    {
        // Fetch claims grouped by status, eager load related models for efficiency
        $pendingClaims = Claim::with(['item.user', 'claimer'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedClaims = Claim::with(['item.user', 'claimer'])
            ->where('status', 'approved')
            ->orderBy('updated_at', 'desc')
            ->get();

        $rejectedClaims = Claim::with(['item.user', 'claimer'])
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('claim-items', compact('pendingClaims', 'approvedClaims', 'rejectedClaims'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,item_id',
            'message' => 'required|string|max:1000',
        ]);

        $claim = new Claim();
        $claim->item_id = $request->item_id;
        $claim->claimer_id = auth()->id();
        $claim->message = $request->input('message', 'Claiming this item.');
        $claim->status = 'pending';
        $claim->save();

        return redirect()->back()->with('success', 'Claim submitted successfully. We will review it soon.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $claim = Claim::findOrFail($id);

        // Optionally, authorize if the current user can update claim status here

        $claim->status = $request->status;
        $claim->save();

        return redirect()->back()->with('success', 'Claim status updated!');
    }
}
