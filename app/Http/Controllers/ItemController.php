<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Claim;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    use AuthorizesRequests;

    // Show lost items paginated (exclude claimed items)
    public function lostIndex(Request $request)
    {
        $query = Item::where('type', 'lost')->where('status', 'available');

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $lostItems = $query->with('user')->orderBy('created_at', 'desc')->paginate(10);

        return view('lost-items', compact('lostItems'));
    }

    // Show found items paginated (exclude claimed items)
    public function foundIndex(Request $request)
    {
        $query = Item::where('type', 'found')->where('status', 'available');

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $foundItems = $query->with('user')->orderBy('created_at', 'desc')->paginate(10);

        return view('found-items', compact('foundItems'));
    }

    // Show form for reporting a lost or found item
    public function report($type)
    {
        if (!in_array($type, ['lost', 'found'])) {
            return redirect()->back()->with('error', 'Invalid item type');
        }

        return view('report-form', compact('type'));
    }

    // Show details of a single item with claims
    public function show($id)
    {
        $item = Item::with(['user', 'claims.claimer'])->findOrFail($id);

        // Check if the logged-in user has claimed this item
        $userHasClaimed = false;
        if (auth()->check()) {
            $userHasClaimed = $item->claims()->where('claimer_id', auth()->id())->exists();
        }

        return view('item-details', compact('item', 'userHasClaimed'));
    }

    // Store a newly reported item (lost or found)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'date_lost_found' => 'required|date',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|max:2048',
            'type' => 'required|in:lost,found',
        ]);

        try {
            $item = new Item();
            $item->title = $validated['title'];
            $item->location = $validated['location'];
            $item->date_lost_found = $validated['date_lost_found'];
            $item->description = $validated['description'] ?? null;
            $item->category = $validated['category'];
            $item->type = $validated['type'];
            $item->status = 'available'; // Set initial status as available
            $item->user_id = auth()->id();

            // Handle image upload if exists
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('items', 'public');
                $item->image_path = $path;
            }

            $item->save();

            Log::info('Item created', [
                'item_id' => $item->item_id,
                'type' => $item->type,
                'title' => $item->title,
                'user_id' => auth()->id()
            ]);

            session()->flash('success', 'Item reported successfully!');
            return redirect()->route($item->type . '.index');

        } catch (\Exception $e) {
            Log::error('Failed to create item', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $validated
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to report item. Please try again.');
        }
    }

    // Edit item
    public function edit($id)
    {
        $item = Item::findOrFail($id);

        // Authorization check
        if ($item->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        return view('edit-item', compact('item'));
    }

    // Update item
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        
        // Authorization check
        if ($item->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|max:2048',
            'date_lost' => 'nullable|date', // For lost items
        ]);

        try {
            $item->fill($validated);

            // Handle date_lost for lost items
            if ($item->type === 'lost' && $request->has('date_lost')) {
                $item->date_lost_found = $validated['date_lost'];
            }

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($item->image_path) {
                    Storage::disk('public')->delete($item->image_path);
                }
                
                $path = $request->file('image')->store('items', 'public');
                $item->image_path = $path;
            }

            $item->save();

            Log::info('Item updated', [
                'item_id' => $item->item_id,
                'title' => $item->title,
                'user_id' => auth()->id()
            ]);

            session()->flash('success', 'Item updated successfully!');
            return redirect()->route($item->type . '.index');

        } catch (\Exception $e) {
            Log::error('Failed to update item', [
                'item_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update item. Please try again.');
        }
    }

    // Delete item
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        
        // Authorization check
        if ($item->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $type = $item->type; // Save type before deletion

        try {
            // Delete image if exists
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            
            // Delete associated claims first
            $item->claims()->delete();
            
            // Delete the item
            $item->delete();

            Log::info('Item deleted', [
                'item_id' => $id,
                'type' => $type,
                'user_id' => auth()->id()
            ]);

            return redirect()->route($type . '.index')->with('success', 'Item deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to delete item', [
                'item_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', 'Failed to delete item. Please try again.');
        }
    }

    // View my reported items (both lost and found)
    public function myItems()
    {
        $userId = auth()->id();

        $items = Item::where('user_id', $userId)
            ->with('claims')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('my-items', compact('items'));
    }

    // Mark item as claimed (this method is now redundant since ClaimController handles this)
    public function markAsClaimed($itemId)
    {
        try {
            DB::beginTransaction();

            $item = Item::with('claims')->findOrFail($itemId);

            // Authorization check
            if (auth()->id() !== $item->user_id && auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized');
            }

            // Check if there's at least one approved claim
            $approvedClaim = $item->claims()->where('status', 'approved')->first();
            
            if (!$approvedClaim) {
                return redirect()->back()->with('error', 'No approved claims found for this item.');
            }

            // Mark item as claimed
            $item->status = 'claimed';
            $item->save();

            // Reject all pending claims
            $item->claims()->where('status', 'pending')->update(['status' => 'rejected']);

            Log::info('Item manually marked as claimed', [
                'item_id' => $itemId,
                'user_id' => auth()->id(),
                'approved_claim_id' => $approvedClaim->claim_id
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Item marked as claimed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to mark item as claimed', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', 'Failed to mark item as claimed. Please try again.');
        }
    }

    // Optional matching functionality
    public function match($id)
    {
        $lostItem = Item::where('type', 'lost')->findOrFail($id);
        
        // Find similar found items based on category and location
        $similarFoundItems = Item::where('type', 'found')
            ->where('status', 'available')
            ->where(function($query) use ($lostItem) {
                $query->where('category', $lostItem->category)
                      ->orWhere('location', 'like', '%' . $lostItem->location . '%');
            })
            ->where('item_id', '!=', $lostItem->item_id)
            ->with('user')
            ->get();

        return view('item-matches', compact('lostItem', 'similarFoundItems'));
    }
}