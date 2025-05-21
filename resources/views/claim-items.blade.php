@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">My Claims</h2>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <ul class="nav nav-tabs" id="claimsTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab" aria-controls="pending" aria-selected="true">
                        Pending 
                        <span class="badge badge-warning">{{ $pendingClaims->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="approved-tab" data-toggle="tab" href="#approved" role="tab" aria-controls="approved" aria-selected="false">
                        Approved 
                        <span class="badge badge-success">{{ $approvedClaims->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="rejected-tab" data-toggle="tab" href="#rejected" role="tab" aria-controls="rejected" aria-selected="false">
                        Rejected 
                        <span class="badge badge-danger">{{ $rejectedClaims->count() }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="claimsTabsContent">
        <!-- Pending Claims Tab -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
            @if($pendingClaims->count() > 0)
                <div class="row">
                    @foreach($pendingClaims as $claim)
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-warning text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ $claim->item->title }}</span>
                                        <small>{{ $claim->created_at->format('M d, Y') }}</small>
                                    </div>
                                </div>
                                
                                @if($claim->item->image_path)
                                    <img src="{{ asset('storage/' . $claim->item->image_path) }}" class="card-img-top" alt="{{ $claim->item->title }}" style="height: 150px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex justify-content-center align-items-center" style="height: 150px;">
                                        <i class="fas fa-box fa-3x text-secondary"></i>
                                    </div>
                                @endif
                                
                                <div class="card-body">
                                    <p class="card-text">
                                        <strong>Status:</strong> 
                                        <span class="badge badge-warning">Pending</span>
                                    </p>
                                    <p class="card-text">
                                        <strong>Item Owner:</strong> {{ $claim->item->user->name }}
                                    </p>
                                    <p class="card-text text-truncate">
                                        <small>{{ Str::limit($claim->message, 50) }}</small>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('items.show', $claim->item->id) }}" class="btn btn-info btn-block btn-sm">View Item</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center">
                    <p class="mb-0">You have no pending claims.</p>
                </div>
            @endif
        </div>
        
        <!-- Approved Claims Tab -->
        <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
            @if($approvedClaims->count() > 0)
                <div class="row">
                    @foreach($approvedClaims as $claim)
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ $claim->item->title }}</span>
                                        <small>{{ $claim->updated_at->format('M d, Y') }}</small>
                                    </div>
                                </div>
                                
                                @if($claim->item->image_path)
                                    <img src="{{ asset('storage/' . $claim->item->image_path) }}" class="card-img-top" alt="{{ $claim->item->title }}" style="height: 150px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex justify-content-center align-items-center" style="height: 150px;">
                                        <i class="fas fa-box fa-3x text-secondary"></i>
                                    </div>
                                @endif
                                
                                <div class="card-body">
                                    <p class="card-text">
                                        <strong>Status:</strong> 
                                        <span class="badge badge-success">Approved</span>
                                    </p>
                                    <p class="card-text">
                                        <strong>Item Owner:</strong> {{ $claim->item->user->name }}
                                    </p>
                                    <p class="card-text">
                                        <strong>Contact:</strong> {{ $claim->item->user->email }}
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('items.show', $claim->item->id) }}" class="btn btn-info btn-block btn-sm">View Item</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center">
                    <p class="mb-0">You have no approved claims.</p>
                </div>
            @endif
        </div>
        
        <!-- Rejected Claims Tab -->
        <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
            @if($rejectedClaims->count() > 0)
                <div class="row">
                    @foreach($rejectedClaims as $claim)
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-danger text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ $claim->item->title }}</span>
                                        <small>{{ $claim->updated_at->format('M d, Y') }}</small>
                                    </div>
                                </div>
                                
                                @if($claim->item->image_path)
                                    <img src="{{ asset('storage/' . $claim->item->image_path) }}" class="card-img-top" alt="{{ $claim->item->title }}" style="height: 150px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex justify-content-center align-items-center" style="height: 150px;">
                                        <i class="fas fa-box fa-3x text-secondary"></i>
                                    </div>
                                @endif
                                
                                <div class="card-body">
                                    <p class="card-text">
                                        <strong>Status:</strong> 
                                        <span class="badge badge-danger">Rejected</span>
                                    </p>
                                    <p class="card-text">
                                        <strong>Item Owner:</strong> {{ $claim->item->user->name }}
                                    </p>
                                    <p class="card-text text-truncate">
                                        <small>{{ Str::limit($claim->message, 50) }}</small>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('items.show', $claim->item->id) }}" class="btn btn-info btn-block btn-sm">View Item</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center">
                    <p class="mb-0">You have no rejected claims.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection