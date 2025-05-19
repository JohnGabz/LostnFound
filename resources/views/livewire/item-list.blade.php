@extends('layouts.app')
@section('content')
<div>
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                {{ request('type') === 'found' ? 'Found Items' : (request('type') === 'lost' ? 'Lost Items' : 'All Items') }}
            </h2>
            
            <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-3 w-full md:w-auto">
                <div class="flex">
                    <button wire:click="setType('all')" class="px-4 py-2 rounded-l-md {{ !request('type') || request('type') === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                        All
                    </button>
                    <button wire:click="setType('lost')" class="px-4 py-2 {{ request('type') === 'lost' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                        Lost
                    </button>
                    <button wire:click="setType('found')" class="px-4 py-2 rounded-r-md {{ request('type') === 'found' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                        Found
                    </button>
                </div>
                
                <a href="{{ route('items.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                    Report Item
                </a>
            </div>
        </div>
        
        <div class="mb-6">
            <div class="relative">
                <input 
                    wire:model.debounce.300ms="search"
                    type="text" 
                    placeholder="Search items..." 
                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <div class="absolute left-3 top-2.5 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($items as $item)
                <div class="bg-white border rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                    <div class="h-48 bg-gray-200 flex items-center justify-center">
                        @if($item->image)
                            <img src="{{ $item->image }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">{{ $item->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $item->category }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $item->type === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($item->type) }}
                            </span>
                        </div>
                        <div class="mt-3">
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">Location:</span> {{ $item->location }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">Date:</span> {{ $item->date->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-xs px-2 py-1 rounded-full {{ $item->status === 'open' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                            <a href="{{ route('items.show', $item) }}" class="text-blue-600 hover:underline text-sm">View Details</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-500 text-lg">No items found.</p>
                    <p class="text-gray-400 mt-1">Try adjusting your search or filter.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection