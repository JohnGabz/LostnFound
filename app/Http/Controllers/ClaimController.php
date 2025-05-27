<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        
        // Fetch claims where the current user is the claimer
        $pendingClaims = Claim::with(['item.user'])
            ->where('claimer_id', $userId)
            ->where('status', 'pending')
            ->where('claimer_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedClaims = Claim::with(['item.user'])
            ->where('claimer_id', $userId)
            ->where('status', 'approved')
            ->where('claimer_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();

        $rejectedClaims = Claim::with(['item.user'])
            ->where('claimer_id', $userId)
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

        $item = Item::findOrFail($request->item_id);

        // Prevent users from claiming their own items
        if ($item->user_id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot claim your own item.');
        }

        // Check if user already has a pending or approved claim for this item
        $existingClaim = Claim::where('item_id', $request->item_id)
            ->where('claimer_id', auth()->id())
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingClaim) {
            $message = $item->type === 'lost' 
                ? 'You have already reported finding this item.' 
                : 'You have already claimed this item.';
            return redirect()->back()->with('error', $message);
        }

        // Check if item is already claimed
        if ($item->status === 'claimed') {
            return redirect()->back()->with('error', 'This item has already been claimed.');
        }

        try {
            $claim = Claim::create([
                'item_id' => $request->item_id,
                'claimer_id' => auth()->id(),
                'message' => $request->input('message'),
                'status' => 'pending',
            ]);

            Log::info('Claim submitted', [
                'claim_id' => $claim->claim_id,
                'item_id' => $request->item_id,
                'item_type' => $item->type,
                'claimer_id' => auth()->id()
            ]);

            $successMessage = $item->type === 'lost' 
                ? 'Thank you for reporting that you found this item! The owner will be notified.' 
                : 'Your ownership claim has been submitted. The finder will review it soon.';

            return redirect()->back()->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Failed to submit claim', [
                'error' => $e->getMessage(),
                'item_id' => $request->item_id,
                'claimer_id' => auth()->id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to submit claim. Please try again.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        try {
            DB::beginTransaction();

            $claim = Claim::with('item')->findOrFail($id);
            
            // Check if user owns the item or is admin
            $user = auth()->user();
            if ($claim->item->user_id !== $user->user_id && $user->role !== 'admin') {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            $oldStatus = $claim->status;
            $claim->status = $request->status;
            $claim->save();

            // Handle status changes
            if ($request->status === 'approved') {
                // Mark item as claimed
                $claim->item->update(['status' => 'claimed']);
                
                // Reject all other pending claims for this item
                Claim::where('item_id', $claim->item_id)
                    ->where('claim_id', '!=', $claim->claim_id)
                    ->where('status', 'pending')
                    ->update(['status' => 'rejected']);

                Log::info('Claim approved and item marked as claimed', [
                    'claim_id' => $claim->claim_id,
                    'item_id' => $claim->item_id,
                    'item_title' => $claim->item->title,
                    'item_type' => $claim->item->type
                ]);
                
                $message = $claim->item->type === 'lost' 
                    ? 'Finder confirmed! The item has been marked as claimed and reunited with its owner.'
                    : 'Ownership confirmed! The item has been marked as claimed and returned to its owner.';
                    
            } elseif ($request->status === 'rejected') {
                // If this was previously approved, we need to unmark the item
                if ($oldStatus === 'approved') {
                    // Check if there are other approved claims for this item
                    $otherApprovedClaims = Claim::where('item_id', $claim->item_id)
                        ->where('claim_id', '!=', $claim->claim_id)
                        ->where('status', 'approved')
                        ->exists();
                    
                    // If no other approved claims, mark item as available
                    if (!$otherApprovedClaims) {
                        $claim->item->update(['status' => 'available']);
                    }
                }
                
                $message = $claim->item->type === 'lost' 
                    ? 'Finder report rejected.' 
                    : 'Ownership claim rejected.';
            } else {
                $message = 'Claim status updated successfully.';
            }

            DB::commit();
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update claim', [
                'claim_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to update claim status. Please try again.');
        }
    }
}