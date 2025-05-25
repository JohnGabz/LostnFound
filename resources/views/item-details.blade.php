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

                                    @if($item->type == 'lost' && auth()->user()?->hasFoundSimilarItem)
                                        <a href="{{ route('items.match', $item) }}" class="btn btn-info mr-2">
                                            <i class="fas fa-link"></i> I Found This
                                        </a>
                                    @endif
                                @endif

                                <a href="{{ $item->type == 'lost' ? route('lost.index') : route('found.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>

                                @if($item->user_id == auth()->id() || auth()->user()?->isAdmin)
                                    <div class="ml-auto">
                                        <a href="{{ route('items.edit', $item) }}" class="btn btn-warning mr-2">
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

                    @if($item->claims->count() > 0 && ($item->user_id == auth()->id() || auth()->user()?->isAdmin))
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
                                                    <td>{{ optional($claim->claimer)->name ?? 'N/A' }}</td>
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

<!-- Delete Modal -->
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
                Are you sure you want to delete this item?
            </div>
            <div class="modal-footer">
                <form action="{{ route('items.destroy', $item) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelector('.custom-file-input')?.addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var label = e.target.nextElementSibling;
        label.innerHTML = fileName;
    });
</script>
@endsection
