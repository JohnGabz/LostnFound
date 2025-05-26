<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ItemController extends Controller
{
    use AuthorizesRequests;

    // Show lost items paginated
    public function lostIndex(Request $request)
    {
        $query = Item::where('type', 'lost');

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $lostItems = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('lost-items', compact('lostItems'));
    }

    // Show found items paginated
    public function foundIndex(Request $request)
    {
        $query = Item::where('type', 'found');

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $foundItems = $query->orderBy('created_at', 'desc')->paginate(10);

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
        // Eager load user and claims with claimer user info
        $item = Item::with(['user', 'claims.claimer'])->findOrFail($id);

        // Optionally, check if the logged-in user has claimed this item
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

        $item = new Item();
        $item->title = $validated['title'];
        $item->location = $validated['location'];
        $item->date_lost_found = $validated['date_lost_found'];
        $item->description = $validated['description'] ?? null;
        $item->category = $validated['category'];
        $item->type = $validated['type'];
        $item->status = 'pending';
        $item->user_id = auth()->id();

        // Handle image upload if exists
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('items', 'public');
            $item->image_path = $path;
        }

        $item->save();

        session()->flash('success', 'Item reported successfully!');
        return redirect()->route($item->type . '.index');
    }

    // Other CRUD methods, assuming you implement them
    public function edit($id)
    {
        $item = Item::findOrFail($id);

        // Authorization check example (optional)
        $this->authorize('update', $item);

        return view('edit-item', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $this->authorize('update', $item);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        $item->fill($validated);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('items', 'public');
            $item->image_path = $path;
        }

        $item->save();

        session()->flash('success', 'Item updated successfully!');
        return redirect()->route($item->type . '.index');
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $this->authorize('delete', $item);

        $type = $item->type; // Save type before deletion

        $item->delete();

        return redirect()->route($type . '.index')->with('success', 'Item deleted successfully!');
    }

    // Optional: View my reported items (both lost and found)
    public function myItems()
    {
        $userId = auth()->id();

        $items = Item::where('user_id', $userId)->orderBy('created_at', 'desc')->paginate(10);

        return view('my-items', compact('items'));
    }

    public function markAsClaimed($itemId)
    {
        $item = Item::with('claims')->findOrFail($itemId);

        if (auth()->id() !== $item->user_id) {
            abort(403, 'Unauthorized');
        }

        $item->status = 'claimed';
        $item->save();

        // Reject all other claims
        foreach ($item->claims as $claim) {
            if ($claim->status !== 'approved') {
                $claim->status = 'rejected';
                $claim->save();
            }
        }

        return redirect()->back()->with('success', 'Item marked as claimed and other claims rejected.');
    }
}
