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
                                @php
                                    $hasImage = $item->image_path && Storage::disk('public')->exists($item->image_path);
                                    $imageUrl = $hasImage ? Storage::url($item->image_path) : null;
                                @endphp

                                @if($hasImage)
                                    <div class="image-container">
                                        <img src="{{ $imageUrl }}" alt="{{ $item->title }}" class="img-fluid rounded"
                                            style="max-width: 100%; height: auto; max-height: 400px; object-fit: cover;"
                                            onload="console.log('Image loaded successfully:', this.src)"
                                            onerror="console.error('Image failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="bg-light d-none justify-content-center align-items-center rounded"
                                            style="height: 300px;">
                                            <div class="text-center">
                                                <i
                                                    class="fas fa-{{ $item->type == 'lost' ? 'search' : 'box' }} fa-4x text-secondary mb-2"></i>
                                                <p class="text-muted">Image failed to load</p>
                                                <small class="text-muted">Path: {{ $item->image_path }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-light d-flex justify-content-center align-items-center rounded"
                                        style="height: 300px;">
                                        <div class="text-center">
                                            <i
                                                class="fas fa-{{ $item->type == 'lost' ? 'search' : 'box' }} fa-4x text-secondary mb-2"></i>
                                            <p class="text-muted">No image available</p>
                                            @if(config('app.debug') && $item->image_path)
                                                <small class="text-muted">Path in DB: {{ $item->image_path }}</small>
                                            @endif
                                        </div>
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
                                    
                                    {{-- Contact Information --}}
                                    @if($item->user && $item->user->shouldShowContact())
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <p class="card-text mb-1">
                                                <small class="text-muted"><strong>Contact:</strong></small>
                                            </p>
                                            <p class="card-text">
                                                <a href="tel:{{ $item->user->public_contact }}" class="text-decoration-none">
                                                    <i class="fas fa-phone text-primary mr-1"></i>
                                                    {{ $item->user->public_contact }}
                                                </a>
                                            </p>
                                            <p class="card-text">
                                                <a href="sms:{{ $item->user->public_contact }}" class="btn btn-sm btn-outline-primary mr-2">
                                                    <i class="fas fa-sms"></i> Send SMS
                                                </a>
                                                <a href="https://wa.me/{{ str_replace(['+', ' ', '-', '(', ')'], '', $item->user->public_contact) }}" 
                                                   target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                                </a>
                                            </p>
                                        </div>
                                    @elseif($item->user && !$item->user->shouldShowContact() && $item->user_id != auth()->id())
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle text-info mr-1"></i>
                                                    Contact via claims/messages system
                                                </small>
                                            </p>
                                        </div>
                                    @endif
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
                                    {{-- Action buttons for non-owners --}}
                                    @if($item->status != 'claimed' && $item->user_id != auth()->id())
                                        @if($item->type == 'lost')
                                            <button type="button" class="btn btn-success mr-2 mb-2" data-toggle="modal"
                                                data-target="#foundThisItemModal">
                                                <i class="fas fa-check-circle"></i> I Found This Item
                                            </button>
                                        @endif

                                        @if($item->type == 'found')
                                            <button type="button" class="btn btn-primary mr-2 mb-2" data-toggle="modal"
                                                data-target="#claimItemModal">
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
                                        <i class="fas fa-check-circle"></i> This item has been successfully claimed and is no
                                        longer available.
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
                                                    <th>Contact</th>
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
                                                        <td>
                                                            @if($claim->claimer && $claim->claimer->shouldShowContact())
                                                                <a href="tel:{{ $claim->claimer->public_contact }}" class="text-decoration-none">
                                                                    <i class="fas fa-phone text-primary mr-1"></i>
                                                                    {{ $claim->claimer->public_contact }}
                                                                </a>
                                                                <br>
                                                                <div class="mt-1">
                                                                    <a href="sms:{{ $claim->claimer->public_contact }}" class="btn btn-xs btn-outline-primary mr-1">SMS</a>
                                                                    <a href="https://wa.me/{{ str_replace(['+', ' ', '-', '(', ')'], '', $claim->claimer->public_contact) }}" 
                                                                       target="_blank" class="btn btn-xs btn-outline-success">WhatsApp</a>
                                                                </div>
                                                            @else
                                                                <small class="text-muted">Contact via system</small>
                                                            @endif
                                                        </td>
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
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteItemModalLabel">Confirm Deletion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete "{{ $item->title }}"? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form action="{{ route('items.destroy', $item) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <<!-- Found This Item Modal -->
        <div class="modal fade" id="foundThisItemModal" tabindex="-1" role="dialog"
            aria-labelledby="foundThisItemModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('claims.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $item->item_id }}">
                        <input type="hidden" name="claim_type" value="found">

                        <div class="modal-header">
                            <h5 class="modal-title" id="foundThisItemModalLabel">Found This Item</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="form-group">
                                <label for="message-found">Details</label>
                                <textarea name="message" id="message-found" rows="4" class="form-control"
                                    required></textarea>
                            </div>

                            <div class="form-group">
                                <label for="image-found">Upload Image (optional)</label>
                                <input type="file" name="image" id="image-found"
                                    class="form-control-file @error('image') is-invalid @enderror" accept="image/*">
                                @error('image')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Upload proof such as photos, screenshots, etc. Max
                                    2MB.</small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Claim Item Modal -->
        <div class="modal fade" id="claimItemModal" tabindex="-1" role="dialog" aria-labelledby="claimItemModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('claims.store') }}" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $item->item_id }}">
                        <input type="hidden" name="claim_type" value="claim"> {{-- Changed to 'claim' here --}}

                        <div class="modal-header">
                            <h5 class="modal-title" id="claimItemModalLabel">Claim This Item</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="form-group">
                                <label for="message-claim">Details</label>
                                <textarea name="message" id="message-claim" rows="4" class="form-control"
                                    required></textarea>
                            </div>

                            <div class="form-group">
                                <label for="image-claim">Upload Image (optional)</label>
                                <input type="file" name="image" id="image-claim"
                                    class="form-control-file @error('image') is-invalid @enderror" accept="image/*">
                                @error('image')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Upload proof such as photos, screenshots, etc. Max
                                    2MB.</small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection

    @section('scripts')
        <script>
            // Debug image loading
            document.addEventListener('DOMContentLoaded', function () {
                const images = document.querySelectorAll('img[src*="storage"]');
                images.forEach(img => {
                    img.addEventListener('error', function () {
                        console.log('Failed to load image:', this.src);
                    });
                    img.addEventListener('load', function () {
                        console.log('Successfully loaded image:', this.src);
                    });
                });
            });
        </script>
    @endsection