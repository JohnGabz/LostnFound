<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    public function show(Item $item)
    {
        // Load necessary relationships
        $item->load(['user', 'claims.claimer']);

        // Check if current user has already claimed this item
        $userHasClaimed = false;
        if (Auth::check()) {
            $userHasClaimed = $item->claims()
                ->where('claimer_id', Auth::id())
                ->exists();
        }

        // Fix image path if it exists
        if ($item->image_path) {
            // Ensure the image file actually exists
            if (!Storage::disk('public')->exists($item->image_path)) {
                // If file doesn't exist, set to null to show placeholder
                $item->image_path = null;
            }
        }

        return view('item-details', compact('item', 'userHasClaimed'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'location' => 'required|string',
            'date_lost_found' => 'nullable|date',
            'description' => 'nullable|string',
            'type' => 'required|in:lost,found',
        ]);

        $item = new Item();
        $item->title = $data['title'];
        $item->category = $data['category'];
        $item->location = $data['location'];
        $item->date_lost_found = $data['date_lost_found'] ?? null;
        $item->description = $data['description'] ?? null;
        $item->type = $data['type']; // Important to assign type!
        $item->user_id = auth()->id();
        $item->status = 'pending';

        // Handle image upload if any
        $item->image_path = $this->handleImageUpload($request->file('image'));

        $item->save();

        // Redirect to the correct list depending on type
        if ($item->type === 'lost') {
            return redirect()->route('lost.index')->with('success', 'Lost item reported successfully.');
        } else {
            return redirect()->route('found.index')->with('success', 'Found item reported successfully.');
        }
    }


    public function update(Request $request, Item $item)
    {
        // Authorization check
        $this->authorize('update', $item);

        // Fix validation rules to match form fields
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'date_lost' => 'nullable|date|before_or_equal:today', // Changed from date_lost_found
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle new image upload first
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }

            $item->image_path = $this->handleImageUpload($request->file('image'));
        }

        // Update the item with validated data
        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'location' => $validated['location'],
        ];

        // Add date field based on item type
        if ($item->type === 'lost' && isset($validated['date_lost'])) {
            $updateData['date_lost'] = $validated['date_lost'];
        }

        // Add image path if it was updated
        if (isset($item->image_path)) {
            $updateData['image_path'] = $item->image_path;
        }

        $item->update($updateData);

        // Redirect back to the appropriate index page
        $redirectRoute = $item->type === 'lost' ? 'lost.index' : 'found.index';

        return redirect()->route($redirectRoute)
            ->with('success', ucfirst($item->type) . ' item updated successfully!');
    }

    private function handleImageUpload($file)
    {
        if (!$file) {
            return null; // If no file was uploaded, return null
        }

        try {
            // Generate unique filename
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            // Store in public disk under 'items' folder
            $path = $file->storeAs('items', $filename, 'public');

            return $path;
        } catch (\Exception $e) {
            \Log::error('Image upload failed: ' . $e->getMessage());
            return null;
        }
    }


    public function lostIndex(Request $request)
    {
        $query = Item::where('type', 'lost')
            ->where('status', '!=', 'claimed')
            ->with('user');

        // Add search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }

        $lostItems = $query->latest()->paginate(12);

        return view('lost-items', compact('lostItems'));
    }

    public function foundIndex(Request $request)
    {
        $query = Item::where('type', 'found')
            ->where('status', '!=', 'claimed')
            ->with('user');

        // Add search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }

        $foundItems = $query->latest()->paginate(12);

        return view('found-items', compact('foundItems'));
    }

    public function myItems()
    {
        $items = Item::where('user_id', Auth::id())
            ->with([
                'claims' => function ($query) {
                    $query->where('status', 'pending');
                }
            ])
            ->latest()
            ->paginate(10);

        return view('my-items', compact('items'));
    }

    public function edit(Item $item)
    {
        $this->authorize('update', $item);
        return view('edit-item', compact('item'));
    }

    public function destroy(Item $item)
    {
        // Authorization check
        $this->authorize('delete', $item);

        try {
            // Delete associated image if exists
            if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
                Storage::disk('public')->delete($item->image_path);
            }

            // Delete associated claims first (if any)
            $item->claims()->delete();

            // Delete the item
            $item->delete();

            // Determine redirect route based on item type
            $redirectRoute = $item->type === 'lost' ? 'lost.index' : 'found.index';

            return redirect()->route($redirectRoute)
                ->with('success', ucfirst($item->type) . ' item deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Item deletion failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete item. Please try again.');
        }
    }

    public function report($type)
    {
        if (!in_array($type, ['lost', 'found'])) {
            abort(404);
        }

        return view('report-form', compact('type'));
    }
}