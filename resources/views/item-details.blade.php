@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ ucfirst($item->type) }} Item Details</h5>
                            <span
                                class="badge badge-{{ $item->status == 'claimed' ? 'success' : ($item->type == 'lost' ? 'danger' : 'primary') }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                @if($item->image_path)
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}"
                                        class="img-fluid rounded" style="max-width: 100%; height: auto;">
                                @else
                                    <div class="bg-light d-flex justify-content-center align-items-center rounded"
                                        style="height: 300px;">
                                        <i
                                            class="fas fa-{{ $item->type == 'lost' ? 'search' : 'box' }} fa-5x text-secondary"></i>
                                    </div>
                                @endif

                                <div class="mt-3">
                                    <p class="card-text mb-1">
                                        <small class="text-muted">Posted by:
                                            {{ optional($item->user)->name ?? 'N/A' }}</small>
                                    </p>
                                    <p class="card-text">
                                        <small class="text-muted">Posted on:
                                            {{ $item->created_at->format('M d, Y g:i A') }}</small>
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
                                        <p class="mb-1"><strong>Date
                                                {{ $item->type == 'lost' ? 'Lost' : 'Found' }}:</strong></p>
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
                                    {{-- Only show action buttons if item is not claimed and user is not the owner --}}
                                    @if($item->status != 'claimed' && $item->user_id != auth()->id())
                                        
                                        {{-- For LOST items: Show "I Found This" button --}}
                                        @if($item->type == 'lost')
                                            <button type="button" class="btn btn-success mr-2 mb-2" data-toggle="modal"
                                                data-target="#foundThisItemModal">
                                                <i class="fas fa-check-circle"></i> I Found This Item
                                            </button>
                                        @endif

                                        {{-- For FOUND items: Show "This is Mine" button --}}
                                        @if($item->type == 'found')
                                            <button type="button" class="btn btn-primary mr-2 mb-2" data-toggle="modal"
                                                data-target="#claimItemModal">
                                                <i class="fas fa-hand-paper"></i> This is Mine
                                            </button>
                                        @endif

                                    @endif

                                    {{-- Back button --}}
                                    <a href="{{ url()->previous() }}" class="btn btn-secondary mr-2 mb-2">
                                        <i class="fas fa-arrow-left"></i> Back to List
                                    </a>

                                    {{-- Owner/Admin actions --}}
                                    @if($item->user_id == auth()->id() || (auth()->user() && auth()->user()->role == 'admin'))
                                        <div class="ml-auto d-flex">
                                            <a href="{{ route('items.edit', $item) }}" class="btn btn-warning mr-2 mb-2">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>

                                            <button type="button" class="btn btn-danger mb-2" data-toggle="modal"
                                                data-target="#deleteItemModal">
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

                                @if($userHasClaimed)
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i> You have already submitted a request for this item.
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Claims section for item owner/admin --}}
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
                                                            <span
                                                                class="badge badge-{{ $claim->status == 'pending' ? 'warning' : ($claim->status == 'approved' ? 'success' : 'danger') }}">
                                                                {{ ucfirst($claim->status) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($claim->status == 'pending')
                                                                <div class="btn-group btn-group-sm">
                                                                    <form
                                                                        action="{{ route('claims.update', ['claim' => $claim->claim_id]) }}"
                                                                        method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status" value="approved">
                                                                        <button type="submit" class="btn btn-success mr-1">
                                                                            {{ $item->type == 'lost' ? 'Confirm Finder' : 'Confirm Owner' }}
                                                                        </button>
                                                                    </form>

                                                                    <form
                                                                        action="{{ route('claims.update', ['claim' => $claim->claim_id]) }}"
                                                                        method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status" value="rejected">
                                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                                    </form>
                                                                </div>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                                                    data-target="#viewClaimModal-{{ $claim->claim_id }}">
                                                                    View Details
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    {{-- View Claim Details Modal --}}
                                                    <div class="modal fade" id="viewClaimModal-{{ $claim->claim_id }}" tabindex="-1" role="dialog"
                                                        aria-labelledby="viewClaimModalLabel-{{ $claim->claim_id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="viewClaimModalLabel-{{ $claim->claim_id }}">
                                                                        {{ $item->type == 'lost' ? 'Finder' : 'Claim' }} Details
                                                                    </h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p><strong>Name:</strong> {{ $claim->claimer->name ?? 'N/A' }}</p>
                                                                    <p><strong>Email:</strong> {{ $claim->claimer->email ?? 'N/A' }}</p>
                                                                    <p><strong>Message:</strong> {{ $claim->message ?? 'No message provided.' }}</p>
                                                                    <p><strong>Status:</strong> {{ ucfirst($claim->status) }}</p>
                                                                    <p><strong>Submitted:</strong> {{ $claim->created_at->format('M d, Y g:i A') }}</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
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

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteItemModal" tabindex="-1" role="dialog" aria-labelledby="deleteItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('items.destroy', $item) }}" method="POST" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteItemModalLabel">Delete Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this item? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Claim Item Modal (for Found Items - "This is Mine") --}}
    <div class="modal fade" id="claimItemModal" tabindex="-1" role="dialog" aria-labelledby="claimItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('claims.store') }}" method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="item_id" value="{{ $item->item_id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="claimItemModalLabel">Claim Your Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Please provide details to prove this item belongs to you.</p>
                    <div class="form-group">
                        <label for="message">Proof of Ownership</label>
                        <textarea name="message" id="message" class="form-control" rows="4"
                            placeholder="Describe the item in detail, when/where you lost it, any unique features, etc." required></textarea>
                        <small class="form-text text-muted">The more details you provide, the easier it will be to verify your ownership.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Submit Claim</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Found This Item Modal (for Lost Items - "I Found This") --}}
    <div class="modal fade" id="foundThisItemModal" tabindex="-1" role="dialog" aria-labelledby="foundThisItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('claims.store') }}" method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="item_id" value="{{ $item->item_id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="foundThisItemModalLabel">I Found This Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Help reunite the owner with their lost item!</p>
                    <div class="form-group">
                        <label for="found_message">Details about Finding the Item</label>
                        <textarea name="message" id="found_message" class="form-control" rows="4"
                            placeholder="Where did you find it? When? Any additional details that might help verify it's the right item..." required></textarea>
                        <small class="form-text text-muted">Your contact information will be shared with the owner once they verify ownership.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Notify Owner</button>
                </div>
            </form>
        </div>
    </div>
@endsection