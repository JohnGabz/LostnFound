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
                            @if($item->image_path)
                                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}" class="img-fluid rounded">
                            @else
                                <div class="bg-light d-flex justify-content-center align-items-center rounded" style="height: 300px;">
                                    <i class="fas fa-{{ $item->type == 'lost' ? 'search' : 'box' }} fa-5x text-secondary"></i>
                                </div>
                            @endif
                            
                            <div class="mt-3">
                                <p class="card-text mb-1">
                                    <small class="text-muted">Posted by: {{ $item->user->name }}</small>
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
                                    <p class="card-text">{{ $item->date_lost ? $item->date_lost->format('M d, Y') : 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Status:</strong></p>
                                    <p class="card-text">{{ ucfirst($item->status) }}</p>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="mb-1"><strong>Description:</strong></p>
                                <p class="card-text">{{ $item->description }}</p>
                            </div>
                            
                            <div class="d-flex">
                                @if($item->status != 'claimed' && $item->user_id != auth()->id())
                                    @if($item->type == 'found' && !$userHasClaimed)
                                        <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#claimItemModal">
                                            <i class="fas fa-hand-holding"></i> Claim This Item
                                        </button>
                                    @endif
                                    
                                    @if($item->type == 'lost' && auth()->user()->hasFoundSimilarItem)
                                        <a href="{{ route('items.match', $item->id) }}" class="btn btn-info mr-2">
                                            <i class="fas fa-link"></i> I Found This
                                        </a>
                                    @endif
                                @endif
                                
                                <a href="{{ $item->type == 'lost' ? route('lost.index') : route('found.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                                
                                @if($item->user_id == auth()->id() || auth()->user()->isAdmin)
                                    <div class="ml-auto">
                                        <a href="{{ route('items.edit', $item->id) }}" class="btn btn-warning mr-2">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteItemModal">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($item->claims->count() > 0 && ($item->user_id == auth()->id() || auth()->user()->isAdmin))
                        <div class="row mt-5">
                            <div class="col-md-12">
                                <h5>Claim Requests ({{ $item->claims->count() }})</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Claimer</th>
                                                <th>Date</th>
                                                <th>Message</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->claims as $claim)
                                                <tr>
                                                    <td>{{ $claim->claimer->name }}</td>
                                                    <td>{{ $claim->created_at->format('M d, Y') }}</td>
                                                    <td>{{ Str::limit($claim->message, 50) }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ 
                                                            $claim->status == 'pending' ? 'warning' : 
                                                            ($claim->status == 'approved' ? 'success' : 'danger') 
                                                        }}">
                                                            {{ ucfirst($claim->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($claim->status == 'pending')
                                                            <div class="btn-group btn-group-sm">
                                                                <form action="{{ route('claims.update', $claim->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="status" value="approved">
                                                                    <button type="submit" class="btn btn-success mr-1">Approve</button>
                                                                </form>
                                                                
                                                                <form action="{{ route('claims.update', $claim->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="status" value="rejected">
                                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#viewClaimModal-{{ $claim->id }}">
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

<!-- Claim Item Modal -->
<div class="modal fade" id="claimItemModal" tabindex="-1" role="dialog" aria-labelledby="claimItemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('claims.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="item_id" value="{{ $item->id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="claimItemModalLabel">Claim: {{ $item->title }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <p class="mb-0">
                            <i class="fas fa-info-circle"></i> 
                            To claim this item, please provide information to verify your ownership. The person who found the item will review your request.
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label for="claim_message">Proof of Ownership <span class="text-danger">*</span></label>
                        <textarea name="message" id="claim_message" rows="4" class="form-control @error('message') is-invalid @enderror" placeholder="Please describe the item in detail and any identifiable features..." required>{{ old('message') }}</textarea>
                        @error('message')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="claim_photo">Upload Photo Proof (Optional)</label>
                        <div class="custom-file">
                            <input type="file" name="photo" id="claim_photo" class="custom-file-input @error('photo') is-invalid @enderror" accept="image/*">
                            <label class="custom-file-label" for="claim_photo">Choose file...</label>
                            @error('photo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <small class="form-text text-muted">
                            Providing photo evidence (e.g., you with the item, receipt) increases your claim's credibility.
                        </small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Submit Claim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Item Modal -->
<div class="modal fade" id="deleteItemModal" tabindex="-1" role="dialog" aria-labelledby="deleteItemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteItemModalLabel">Delete Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item? This action cannot be undone.</p>
                <p class="mb-0 font-weight-bold">{{ $item->title }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('items.destroy', $item->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@foreach($item->claims as $claim)
    <!-- View Claim Modal -->
    <div class="modal fade" id="viewClaimModal-{{ $claim->id }}" tabindex="-1" role="dialog" aria-labelledby="viewClaimModalLabel-{{ $claim->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewClaimModalLabel-{{ $claim->id }}">Claim Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Claimer:</strong> {{ $claim->claimer->name }}</p>
                    <p><strong>Date Submitted:</strong> {{ $claim->created_at->format('M d, Y g:i A') }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge badge-{{ 
                            $claim->status == 'pending' ? 'warning' : 
                            ($claim->status == 'approved' ? 'success' : 'danger') 
                        }}">
                            {{ ucfirst($claim->status) }}
                        </span>
                    </p>
                    
                    <div class="mt-3">
                        <p><strong>Message:</strong></p>
                        <div class="p-3 bg-light rounded">
                            {{ $claim->message }}
                        </div>
                    </div>
                    
                    @if($claim->photo_path)
                        <div class="mt-3">
                            <p><strong>Photo Proof:</strong></p>
                            <img src="{{ asset('storage/' . $claim->photo_path) }}" alt="Proof" class="img-fluid rounded">
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection

@section('scripts')
<script>
    // Display the name of the selected file
    document.querySelector('.custom-file-input')?.addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var label = e.target.nextElementSibling;
        label.innerHTML = fileName;
    });
</script>
@endsection