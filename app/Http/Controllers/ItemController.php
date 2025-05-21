<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ItemController extends Controller
{
    public function lostIndex()
    {
        // Sample data for testing
        $lostItems = collect([
            (object) [
                'id' => 1,
                'title' => 'iPhone 13 Pro',
                'location' => 'Library, 2nd Floor',
                'date_lost' => now()->subDays(2),
                'description' => 'Black iPhone with red case and cracked screen',
                'category' => 'Electronics',
                'image_path' => null,
                'created_at' => now()->subDays(2),
                'status' => 'lost'
            ],
            (object) [
                'id' => 2,
                'title' => 'MacBook Charger',
                'location' => 'Computer Lab',
                'date_lost' => now()->subDays(5),
                'description' => 'Apple MacBook charger with a blue sticker',
                'category' => 'Electronics',
                'image_path' => null,
                'created_at' => now()->subDays(5),
                'status' => 'lost'
            ],
        ]);
        
        // Paginate manually for testing
        $lostItems = new LengthAwarePaginator(
            $lostItems, 
            count($lostItems), 
            10, 
            1, 
            ['path' => request()->url()]
        );
        
        return view('lost-items', compact('lostItems'));
    }

    public function foundIndex()
    {
        // Sample data for testing
        $foundItems = collect([
            (object) [
                'id' => 3,
                'title' => 'Blue Backpack',
                'location' => 'Cafeteria',
                'date_lost' => null,
                'description' => 'Nike blue backpack with math textbooks inside',
                'category' => 'Bags',
                'image_path' => null,
                'created_at' => now()->subDays(1),
                'status' => 'found'
            ],
            (object) [
                'id' => 4,
                'title' => 'Student ID Card',
                'location' => 'Main Hall',
                'date_lost' => null,
                'description' => 'Student ID card for John Smith',
                'category' => 'ID/Documents',
                'image_path' => null,
                'created_at' => now()->subDays(3),
                'status' => 'found'
            ],
        ]);
        
        // Paginate manually for testing
        $foundItems = new LengthAwarePaginator(
            $foundItems, 
            count($foundItems), 
            10, 
            1, 
            ['path' => request()->url()]
        );
        
        return view('found-items', compact('foundItems'));
    }

    public function report($type)
    {
        if (!in_array($type, ['lost', 'found'])) {
            return redirect()->back()->with('error', 'Invalid item type');
        }
        
        return view('report-form', compact('type'));
    }

    public function show($id)
    {
        // Sample item for testing
        $item = (object) [
            'id' => $id,
            'title' => 'Student ID Card',
            'type' => request('type', 'found'),
            'location' => 'Main Hall',
            'date_lost' => now()->subDays(3),
            'description' => 'Student ID card for John Smith. Found near the cafeteria entrance.',
            'category' => 'ID/Documents',
            'image_path' => null,
            'created_at' => now()->subDays(3),
            'status' => 'found',
            'user' => (object) [
                'id' => 1,
                'name' => 'Jane Doe',
                'email' => 'jane@example.com'
            ],
            'claims' => collect([
                (object) [
                    'id' => 1,
                    'status' => 'pending',
                    'message' => 'This is my ID card. I lost it yesterday. My name is John Smith.',
                    'created_at' => now()->subDay(),
                    'updated_at' => now()->subDay(),
                    'photo_path' => null,
                    'claimer' => (object) [
                        'id' => 2,
                        'name' => 'John Smith'
                    ]
                ]
            ])
        ];
        
        $userHasClaimed = false;
        
        return view('item-details', compact('item', 'userHasClaimed'));
    }
    
    public function store(Request $request)
    {
        // Validate and store demo
        session()->flash('success', 'Item reported successfully!');
        return redirect()->route($request->type == 'lost' ? 'lost.index' : 'found.index');
    }
    
    public function myItems()
    {
        // Dummy method for testing
        return view('found-items', ['foundItems' => new LengthAwarePaginator([], 0, 10, 1)]);
    }
    
    public function edit($id)
    {
        // Dummy method for testing
        return redirect()->back();
    }
    
    public function update(Request $request, $id)
    {
        // Dummy method for testing
        return redirect()->back();
    }
    
    public function destroy($id)
    {
        // Dummy method for testing
        return redirect()->back()->with('success', 'Item deleted successfully!');
    }
    
    public function match($id)
    {
        // Dummy method for testing
        return redirect()->back();
    }
}