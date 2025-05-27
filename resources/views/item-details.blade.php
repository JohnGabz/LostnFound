@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ ucfirst($item->type) }} Item Details</h5>
                            <span class="badge badge-{{ $item->status == 'claimed' ? 'success' : ($item->type == 'lost' ? 'danger' : 'primary') }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                @if($item->image_path && Storage::disk('public')->exists($item->image_path))
                                    <img src="{{ Storage::url($item->image_path) }}" 
                                         alt="{{ $item->title }}"
                                         class="img-fluid rounded" 
                                         style="max-width: 100%; height: auto; max-height: 400px; object-fit: cover;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="bg-light d-none justify-content-center align-items-center rounded" style="height: 300px;">
                                        <div class="text-center">
                                            <i class="fas fa-{{ $item->type == 'lost' ? 'search' : 'box' }} fa-4x text-secondary mb-2"></i>
                                            <p class="text-muted">Image not available</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-light d-flex justify-content-center align-items-center rounded" style="height: 300px;">
                                        <div class="text-center">
                                            <i class="fas fa-{{ $item->type == 'lost' ? 'search' : 'box' }} fa-4x text-secondary mb-2"></i>
                                            <p class="text-muted">No image available</p>
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-3">
                                    <p class="card-text mb-1">
                                        <small class="text-muted">Posted by: {{ optional($item->user)->name ?? 'N/A' }}</small>
                                    </p>
                                    <p class="card-text">
                                        <small class="text-muted">Posted on: {{ $item->created_at->format('M d, Y g:i A') }}</small>
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <h4 class="card-title mb-3">{{ $item->title }}</h4>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Category:</strong></p>
                                        <p class="card-text">{{ $item->category }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Location:</strong></p>
                                        <p class="card-text">{{ $item->location }}</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Date {{ $item->type == 'lost' ? 'Lost' : 'Found' }}:</strong></p>
                                        <p class="card-text">
                                            {{ $item->date_lost_found ? $item->date_lost_found->format('M d, Y') : 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Status:</strong></p>
                                        <p class="card-text">{{ ucfirst($item->status) }}</p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <p class="mb-1"><strong>Description:</strong></p>
                                    <p class="card-text">{{ $item->description ?? 'No description provided.' }}</p>
                                </div>

                                <div class="d-flex flex-wrap">
                                    {{-- Action buttons for non-owners --}}
                                    @if($item->status != 'claimed' && $item->user_id != auth()->id())
                                        @if($item->type == 'lost')
                                            <button type="button" class="btn btn-success mr-2 mb-2" data-toggle="modal" data-target="#foundThisItemModal">
                                                <i class="fas fa-check-circle"></i> I Found This Item
                                            </button>
                                        @endif

                                        @if($item->type == 'found')
                                            <button type="button" class="btn btn-primary mr-2 mb-2" data-toggle="modal" data-target="#claimItemModal">
                                                <i class="fas fa-hand-paper"></i> This is Mine
                                            </button>
                                        @endif
                                    @endif

                                    {{-- Fixed Back button - now goes to correct list based on item type --}}
                                    @if($item->type == 'lost')
                                        <a href="{{ route('lost.index') }}" class="btn btn-secondary mr-2 mb-2">
                                            <i class="fas fa-arrow-left"></i> Back to Lost Items
                                        </a>
                                    @else
                                        <a href="{{ route('found.index') }}" class="btn btn-secondary mr-2 mb-2">
                                            <i class="fas fa-arrow-left"></i> Back to Found Items
                                        </a>
                                    @endif

                                    {{-- Owner/Admin actions --}}
                                    @if($item->user_id == auth()->id() || (auth()->user() && auth()->user()->role == 'admin'))
                                        <div class="ml-auto d-flex">
                                            <a href="{{ route('items.edit', $item) }}" class="btn btn-warning mr-2 mb-2">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-danger mb-2" data-toggle="modal" data-target="#deleteItemModal">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                {{-- Status messages --}}
                                @if($item->status == 'claimed')
                                    <div class="alert alert-success mt-3">
                                        <i class="fas fa-check-circle"></i> This item has been successfully claimed and is no longer available.
                                    </div>
                                @endif

                                @if(isset($userHasClaimed) && $userHasClaimed)
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i> You have already submitted a request for this item.
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Claims section --}}
                        @if($item->claims->count() > 0 && ($item->user_id == auth()->id() || (auth()->user() && auth()->user()->role == 'admin')))
                            <div class="row mt-5">
                                <div class="col-md-12">
                                    <h5>
                                        @if($item->type == 'lost')
                                            People Who Found This Item ({{ $item->claims->count() }})
                                        @else
                                            Ownership Claims ({{ $item->claims->count() }})
                                        @endif
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>{{ $item->type == 'lost' ? 'Finder' : 'Claimer' }}</th>
                                                    <th>Date</th>
                                                    <th>Message</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($item->claims as $claim)
                                                    <tr>
                                                        <td>{{ optional($claim->claimer)->name ?? 'N/A' }}</td>
                                                        <td>{{ $claim->created_at->format('M d, Y') }}</td>
                                                        <td>{{ Str::limit($claim->message, 50) }}</td>
                                                        <td>
                                                            <span class="badge badge-{{ $claim->status == 'pending' ? 'warning' : ($claim->status == 'approved' ? 'success' : 'danger') }}">
                                                                {{ ucfirst($claim->status) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($claim->status == 'pending')
                                                                <div class="btn-group btn-group-sm">
                                                                    <form action="{{ route('claims.update', ['claim' => $claim->claim_id]) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status" value="approved">
                                                                        <button type="submit" class="btn btn-success mr-1">
                                                                            {{ $item->type == 'lost' ? 'Confirm Finder' : 'Confirm Owner' }}
                                                                        </button>
                                                                    </form>
                                                                    <form action="{{ route('claims.update', ['claim' => $claim->claim_id]) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status" value="rejected">
                                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                                    </form>
                                                                </div>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#viewClaimModal-{{ $claim->claim_id }}">
                                                                    View Details
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
// Debug image loading
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[src*="storage"]');
    images.forEach(img => {
        img.addEventListener('error', function() {
            console.log('Failed to load image:', this.src);
        });
        img.addEventListener('load', function() {
            console.log('Successfully loaded image:', this.src);
        });
    });
});
</script>
@endsection