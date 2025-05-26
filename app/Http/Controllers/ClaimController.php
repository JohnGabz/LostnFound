<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Log;

class ClaimController extends Controller
{
    // Helper method to log user actions
    private function logAction(string $action, ?string $details = null): void
    {
        Log::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
        ]);
    }

    public function index()
    {
        $userId = auth()->id();

        $pendingClaims = Claim::with(['item.user', 'claimer'])
            ->where('status', 'pending')
            ->where('claimer_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedClaims = Claim::with(['item.user', 'claimer'])
            ->where('status', 'approved')
            ->where('claimer_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();

        $rejectedClaims = Claim::with(['item.user', 'claimer'])
            ->where('status', 'rejected')
            ->where('claimer_id', $userId)
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

        $existingClaim = Claim::where('item_id', $request->item_id)
            ->where('claimer_id', auth()->id())
            ->where('status', 'pending') // optional: restrict to only pending duplicates
            ->first();

        if ($existingClaim) {
            return redirect()->back()->with('error', 'You have already claimed this item.');
        }

        $claim = new Claim();
        $claim->item_id = $request->item_id;
        $claim->claimer_id = auth()->id();
        $claim->message = $request->input('message', 'Claiming this item.');
        $claim->status = 'pending';
        $claim->save();

        $this->logAction('Created claim', "Claim ID: {$claim->id}, Item ID: {$claim->item_id}");

        return redirect()->back()->with('success', 'Claim submitted successfully. We will review it soon.');
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $claim = Claim::findOrFail($id);
        $oldStatus = $claim->status;
        $claim->status = $request->status;
        $claim->save();

        $this->logAction('Updated claim status', "Claim ID: {$claim->id}, From: {$oldStatus} To: {$claim->status}");

        return redirect()->back()->with('success', 'Claim status updated!');
    }
}
