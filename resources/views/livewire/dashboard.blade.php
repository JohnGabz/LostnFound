@extends('layouts.app')
@section('content')
<div>
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
            <div>
                <a href="{{ route('items.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Report Item
                </a>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-100 p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold text-blue-800 mb-2">Lost Items</h3>
                <p class="text-3xl font-bold text-blue-900">{{ $lostItemsCount }}</p>
                <p class="text-sm text-blue-700 mt-2">Items reported as lost</p>
                <div class="mt-4">
                    <a href="{{ route('items.index', ['type' => 'lost']) }}" class="text-blue-600 hover:underline text-sm font-medium">
                        View all lost items →
                    </a>
                </div>
            </div>
            
            <div class="bg-green-100 p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold text-green-800 mb-2">Found Items</h3>
                <p class="text-3xl font-bold text-green-900">{{ $foundItemsCount }}</p>
                <p class="text-sm text-green-700 mt-2">Items reported as found</p>
                <div class="mt-4">
                    <a href="{{ route('items.index', ['type' => 'found']) }}" class="text-green-600 hover:underline text-sm font-medium">
                        View all found items →
                    </a>
                </div>
            </div>
            
            <div class="bg-purple-100 p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold text-purple-800 mb-2">My Claims</h3>
                <p class="text-3xl font-bold text-purple-900">{{ $myClaimsCount }}</p>
                <p class="text-sm text-purple-700 mt-2">Items you've claimed</p>
                <div class="mt-4">
                    <a href="{{ route('claims.index') }}" class="text-purple-600 hover:underline text-sm font-medium">
                        View all my claims →
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Activity</h2>
        
        @if(count($recentItems) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Item
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Location
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentItems as $item)
                            <tr>
                                <td class="py-3 px-4 border-b border-gray-200">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $item->type === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst($item->type) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 border-b border-gray-200">{{ $item->name }}</td>
                                <td class="py-3 px-4 border-b border-gray-200">{{ $item->location }}</td>
                                <td class="py-3 px-4 border-b border-gray-200">{{ $item->created_at->format('M d, Y') }}</td>
                                <td class="py-3 px-4 border-b border-gray-200">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $item->status === 'open' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 border-b border-gray-200">
                                    <a href="{{ route('items.show', $item) }}" class="text-blue-600 hover:underline">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 italic">No recent activity to show.</p>
        @endif
    </div>
</div>
@endsection