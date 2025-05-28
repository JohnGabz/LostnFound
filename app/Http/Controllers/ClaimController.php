<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LaravelLog;

class ClaimController extends Controller
{
    /**
     * Display user's claims organized by status
     */
    public function index()
    {
        $userId = auth()->id();

        $pendingClaims = Claim::with(['item.user'])
            ->where('claimer_id', $userId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedClaims = Claim::with(['item.user'])
            ->where('claimer_id', $userId)
            ->where('status', 'approved')
            ->orderBy('updated_at', 'desc')
            ->get();

        $rejectedClaims = Claim::with(['item.user'])
            ->where('claimer_id', $userId)
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('claim-items', compact('pendingClaims', 'approvedClaims', 'rejectedClaims'));
    }

    /**
     * Store a new claim
     */
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

        // Check for existing claims
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
                'item_id' => $item->item_id,
                'claimer_id' => auth()->id(),
                'message' => $request->input('message'),
                'status' => 'pending',
            ]);

            LaravelLog::info('Claim submitted', [
                'claim_id' => $claim->claim_id,
                'item_id' => $item->item_id,
                'item_type' => $item->type,
                'claimer_id' => auth()->id(),
            ]);

            $this->logAction('Claim submitted', "Claim ID: {$claim->claim_id} for Item: {$item->title}");

            // Wrap notification in try/catch
            try {
                $this->createNotification(
                    $item->user_id,
                    $item->type === 'lost'
                    ? 'New Found Report'
                    : 'New Ownership Claim',
                    $item->type === 'lost'
                    ? "Someone reported finding your lost item: {$item->title}"
                    : "Someone claimed ownership of your found item: {$item->title}",
                    'claim',
                    route('item.show', $item->item_id)
                );
            } catch (\Exception $e) {
                LaravelLog::error('Notification creation failed', [
                    'error' => $e->getMessage(),
                    'item_id' => $item->item_id,
                ]);
            }

            $successMessage = $item->type === 'lost'
                ? 'Thank you for reporting that you found this item! The owner will be notified.'
                : 'Your ownership claim has been submitted. The finder will review it soon.';

            return redirect()->back()->with('success', $successMessage);
        } catch (\Exception $e) {
            LaravelLog::error('Failed to submit claim', [
                'error' => $e->getMessage(),
                'item_id' => $item->item_id,
                'claimer_id' => auth()->id(),
            ]);

            $this->logAction('Failed to submit claim', "Item ID: {$item->item_id}, Error: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to submit claim. Please try again.');
        }

    }

    /**
     * Update claim status (approve/reject)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        try {
            DB::beginTransaction();

            $claim = Claim::with('item')->findOrFail($id);
            $user = auth()->user();

            // Authorization check
            if ($claim->item->user_id !== $user->user_id && $user->role !== 'admin') {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            $oldStatus = $claim->status;
            $claim->status = $request->status;
            $claim->save();

            if ($request->status === 'approved') {
                // Mark item as claimed
                $claim->item->update(['status' => 'claimed']);

                // Reject other pending claims for the same item
                Claim::where('item_id', $claim->item_id)
                    ->where('claim_id', '!=', $claim->claim_id)
                    ->where('status', 'pending')
                    ->update(['status' => 'rejected']);

                LaravelLog::info('Claim approved and item marked as claimed', [
                    'claim_id' => $claim->claim_id,
                    'item_id' => $claim->item_id,
                    'item_title' => $claim->item->title,
                    'item_type' => $claim->item->type
                ]);

                $this->logAction('Claim approved', "Claim ID: {$claim->claim_id}");

                $message = $claim->item->type === 'lost'
                    ? 'Finder confirmed! The item has been marked as claimed and reunited with its owner.'
                    : 'Ownership confirmed! The item has been marked as claimed and returned to its owner.';
            } elseif ($request->status === 'rejected') {
                // If rejecting a previously approved claim, check if item should be marked as available
                if ($oldStatus === 'approved') {
                    $otherApprovedClaims = Claim::where('item_id', $claim->item_id)
                        ->where('claim_id', '!=', $claim->claim_id)
                        ->where('status', 'approved')
                        ->exists();

                    if (!$otherApprovedClaims) {
                        $claim->item->update(['status' => 'available']);
                    }
                }

                $this->logAction('Claim rejected', "Claim ID: {$claim->claim_id}");

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

            LaravelLog::error('Failed to update claim', [
                'claim_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to update claim status. Please try again.');
        }
    }

    /**
     * Log user actions
     */
    private function logAction(string $action, ?string $details = null): void
    {
        Log::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
        ]);
    }
}