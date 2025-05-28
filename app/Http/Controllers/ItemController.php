<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Models\Log;
use App\Models\Notification;
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

        // Log the view action
        $this->logAction('Viewed item details', "Item ID: {$item->item_id}, Title: {$item->title}");

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

        try {
            $item = new Item();
            $item->title = $data['title'];
            $item->category = $data['category'];
            $item->location = $data['location'];
            $item->date_lost_found = $data['date_lost_found'] ?? null;
            $item->description = $data['description'] ?? null;
            $item->type = $data['type'];
            $item->user_id = auth()->id();
            $item->status = 'pending';

            // Handle image upload if any
            $item->image_path = $this->handleImageUpload($request->file('image'));

            $item->save();

            // Log the action
            $this->logAction('Item created', "Item ID: {$item->item_id}, Type: {$item->type}, Title: {$item->title}");

            // Create notification for admin users about new item posting
            $this->notifyAdmins(
                'New Item Posted',
                "A new {$item->type} item '{$item->title}' has been posted by " . auth()->user()->name,
                'item_posted',
                route('item.show', $item->item_id)
            );

            // Redirect to the correct list depending on type
            if ($item->type === 'lost') {
                return redirect()->route('lost.index')->with('success', 'Lost item reported successfully.');
            } else {
                return redirect()->route('found.index')->with('success', 'Found item reported successfully.');
            }
        } catch (\Exception $e) {
            $this->logAction('Failed to create item', "Type: {$data['type']}, Error: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to create item. Please try again.');
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
            'date_lost' => 'nullable|date|before_or_equal:today',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $oldData = $item->toArray(); // Store old data for logging

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

            // Log the update action
            $changes = array_diff_assoc($updateData, array_intersect_key($oldData, $updateData));
            $changedFields = implode(', ', array_keys($changes));
            $this->logAction('Item updated', "Item ID: {$item->item_id}, Changed fields: {$changedFields}");

            // Notify users who have claimed this item about the update
            $this->notifyClaimers($item, 'Item Updated', "The item '{$item->title}' you have claimed has been updated by the owner.");

            // Redirect back to the appropriate index page
            $redirectRoute = $item->type === 'lost' ? 'lost.index' : 'found.index';

            return redirect()->route($redirectRoute)
                ->with('success', ucfirst($item->type) . ' item updated successfully!');
        } catch (\Exception $e) {
            $this->logAction('Failed to update item', "Item ID: {$item->item_id}, Error: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Failed to update item. Please try again.');
        }
    }

    private function handleImageUpload($file)
    {
        if (!$file) {
            return null;
        }

        try {
            // Generate unique filename
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            // Store in public disk under 'items' folder
            $path = $file->storeAs('items', $filename, 'public');

            $this->logAction('Image uploaded', "File: {$filename}");
            return $path;
        } catch (\Exception $e) {
            $this->logAction('Image upload failed', "Error: {$e->getMessage()}");
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

            // Log search action
            $this->logAction('Searched lost items', "Query: {$search}");
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

            // Log search action
            $this->logAction('Searched found items', "Query: {$search}");
        }

        $foundItems = $query->latest()->paginate(12);

        return view('found-items', compact('foundItems'));
    }

    public function myItems()
    {
        $userId = auth()->id();
        
        $lostItems = Item::where('type', 'lost')
            ->where('user_id', $userId)
            ->latest()
            ->get();
            
        $foundItems = Item::where('type', 'found')
            ->where('user_id', $userId)
            ->latest()
            ->get();
            
        $claimedItems = Item::where('status', 'claimed')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        // Log the action
        $this->logAction('Viewed my items');

        return view('my-items', compact('lostItems', 'foundItems', 'claimedItems'));
    }

    public function edit(Item $item)
    {
        $this->authorize('update', $item);
        
        // Log the action
        $this->logAction('Accessed item edit form', "Item ID: {$item->item_id}");
        
        return view('edit-item', compact('item'));
    }

    public function destroy(Item $item)
    {
        // Authorization check
        $this->authorize('delete', $item);

        try {
            $itemTitle = $item->title;
            $itemId = $item->item_id;
            $itemType = $item->type;

            // Get all claimers before deletion for notifications
            $claimers = $item->claims()->with('claimer')->get();

            // Delete associated image if exists
            if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
                Storage::disk('public')->delete($item->image_path);
            }

            // Delete associated claims first (if any)
            $item->claims()->delete();

            // Delete the item
            $item->delete();

            // Log the deletion
            $this->logAction('Item deleted', "Item ID: {$itemId}, Title: {$itemTitle}, Type: {$itemType}");

            // Notify all claimers that the item has been deleted
            foreach ($claimers as $claim) {
                $this->createNotification(
                    $claim->claimer_id,
                    'Item Deleted',
                    "The item '{$itemTitle}' you claimed has been deleted by the owner.",
                    'item_deleted',
                    route('claims.index')
                );
            }

            // Determine redirect route based on item type
            $redirectRoute = $itemType === 'lost' ? 'lost.index' : 'found.index';

            return redirect()->route($redirectRoute)
                ->with('success', ucfirst($itemType) . ' item deleted successfully!');

        } catch (\Exception $e) {
            $this->logAction('Failed to delete item', "Item ID: {$item->item_id}, Error: {$e->getMessage()}");
            \Log::error('Item deletion failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete item. Please try again.');
        }
    }

    public function report($type)
    {
        if (!in_array($type, ['lost', 'found'])) {
            abort(404);
        }

        // Log access to report form
        $this->logAction('Accessed report form', "Type: {$type}");

        return view('report-form', compact('type'));
    }

    /**
     * Log user actions
     */
    private function logAction(string $action, ?string $details = null): void
    {
        if (auth()->check()) {
            Log::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'details' => $details,
            ]);
        }
    }

    /**
     * Create notification for user
     */
    private function createNotification(int $userId, string $title, string $message, string $type, ?string $relatedUrl = null): void
    {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'related_url' => $relatedUrl,
            'is_read' => false,
        ]);
    }

    /**
     * Notify admin users
     */
    private function notifyAdmins(string $title, string $message, string $type, ?string $relatedUrl = null): void
    {
        $adminUsers = User::where('role', 'admin')->get();
        
        foreach ($adminUsers as $admin) {
            $this->createNotification($admin->user_id, $title, $message, $type, $relatedUrl);
        }
    }

    /**
     * Notify users who have claimed this item
     */
    private function notifyClaimers(Item $item, string $title, string $message): void
    {
        $claims = $item->claims()->where('status', 'pending')->get();
        
        foreach ($claims as $claim) {
            $this->createNotification(
                $claim->claimer_id,
                $title,
                $message,
                'item_updated',
                route('item.show', $item->item_id)
            );
        }
    }
}