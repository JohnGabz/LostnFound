@extends('layouts.app')

@section('title', 'My Claims')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">My Claims & Reports</h4>
                    <p class="text-muted mb-0">Track your ownership claims and found item reports</p>
                </div>

                <div class="card-body">
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs" id="claimsTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                                Pending <span class="badge badge-warning ml-1">{{ $pendingClaims->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="approved-tab" data-toggle="tab" href="#approved" role="tab">
                                Approved <span class="badge badge-success ml-1">{{ $approvedClaims->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="rejected-tab" data-toggle="tab" href="#rejected" role="tab">
                                Rejected <span class="badge badge-danger ml-1">{{ $rejectedClaims->count() }}</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="claimsTabContent">
                        <!-- Pending Claims -->
                        <div class="tab-pane fade show active" id="pending" role="tabpanel">
                            <div class="mt-4">
                                @if($pendingClaims->count() > 0)
                                    <div class="row">
                                        @foreach($pendingClaims as $claim)
                                            <div class="col-md-6 mb-4">
                                                <div class="card border-warning">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <h6 class="card-title">
                                                                    <a href="{{ route('items.show', $claim->item->item_id) }}" class="text-decoration-none">
                                                                        {{ $claim->item->title }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-1">
                                                                    <i class="fas fa-{{ $claim->item->type == 'lost' ? 'search' : 'box' }}"></i>
                                                                    {{ ucfirst($claim->item->type) }} Item
                                                                </p>
                                                                <p class="text-muted mb-2">
                                                                    <i class="fas fa-map-marker-alt"></i> {{ $claim->item->location }}
                                                                </p>
                                                            </div>
                                                            <span class="badge badge-warning">Pending</span>
                                                        </div>
                                                        
                                                        <p class="card-text">
                                                            <strong>Your {{ $claim->item->type == 'lost' ? 'Finding Report' : 'Ownership Claim' }}:</strong><br>
                                                            {{ Str::limit($claim->message, 100) }}
                                                        </p>
                                                        
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="text-muted">
                                                                Submitted {{ $claim->created_at->diffForHumans() }}
                                                            </small>
                                                            <a href="{{ route('items.show', $claim->item->item_id) }}" class="btn btn-sm btn-outline-primary">
                                                                View Details
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No Pending Claims</h5>
                                        <p class="text-muted">You don't have any pending claims or reports at the moment.</p>
                                        <a href="{{ route('lost.index') }}" class="btn btn-primary mr-2">Browse Lost Items</a>
                                        <a href="{{ route('found.index') }}" class="btn btn-success">Browse Found Items</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Approved Claims -->
                        <div class="tab-pane fade" id="approved" role="tabpanel">
                            <div class="mt-4">
                                @if($approvedClaims->count() > 0)
                                    <div class="row">
                                        @foreach($approvedClaims as $claim)
                                            <div class="col-md-6 mb-4">
                                                <div class="card border-success">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <h6 class="card-title">
                                                                    <a href="{{ route('items.show', $claim->item->item_id) }}" class="text-decoration-none">
                                                                        {{ $claim->item->title }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-1">
                                                                    <i class="fas fa-{{ $claim->item->type == 'lost' ? 'search' : 'box' }}"></i>
                                                                    {{ ucfirst($claim->item->type) }} Item
                                                                </p>
                                                                <p class="text-muted mb-2">
                                                                    <i class="fas fa-map-marker-alt"></i> {{ $claim->item->location }}
                                                                </p>
                                                            </div>
                                                            <span class="badge badge-success">Approved</span>
                                                        </div>
                                                        
                                                        <div class="alert alert-success">
                                                            <i class="fas fa-check-circle"></i>
                                                            @if($claim->item->type == 'lost')
                                                                <strong>Great job!</strong> You helped reunite this item with its owner.
                                                            @else
                                                                <strong>Congratulations!</strong> Your ownership has been verified.
                                                            @endif
                                                        </div>
                                                        
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="text-muted">
                                                                Approved {{ $claim->updated_at->diffForHumans() }}
                                                            </small>
                                                            <a href="{{ route('items.show', $claim->item->item_id) }}" class="btn btn-sm btn-outline-success">
                                                                View Details
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No Approved Claims</h5>
                                        <p class="text-muted">You haven't had any claims approved yet.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Rejected Claims -->
                        <div class="tab-pane fade" id="rejected" role="tabpanel">
                            <div class="mt-4">
                                @if($rejectedClaims->count() > 0)
                                    <div class="row">
                                        @foreach($rejectedClaims as $claim)
                                            <div class="col-md-6 mb-4">
                                                <div class="card border-danger">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <h6 class="card-title">
                                                                    <a href="{{ route('items.show', $claim->item->item_id) }}" class="text-decoration-none">
                                                                        {{ $claim->item->title }}
                                                                    </a>
                                                                </h6>
                                                                <p class="text-muted mb-1">
                                                                    <i class="fas fa-{{ $claim->item->type == 'lost' ? 'search' : 'box' }}"></i>
                                                                    {{ ucfirst($claim->item->type) }} Item
                                                                </p>
                                                                <p class="text-muted mb-2">
                                                                    <i class="fas fa-map-marker-alt"></i> {{ $claim->item->location }}
                                                                </p>
                                                            </div>
                                                            <span class="badge badge-danger">Rejected</span>
                                                        </div>
                                                        
                                                        <p class="card-text">
                                                            <strong>Your {{ $claim->item->type == 'lost' ? 'Finding Report' : 'Ownership Claim' }}:</strong><br>
                                                            {{ Str::limit($claim->message, 100) }}
                                                        </p>
                                                        
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="text-muted">
                                                                Rejected {{ $claim->updated_at->diffForHumans() }}
                                                            </small>
                                                            <a href="{{ route('items.show', $claim->item->item_id) }}" class="btn btn-sm btn-outline-danger">
                                                                View Details
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-times-circle fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No Rejected Claims</h5>
                                        <p class="text-muted">You haven't had any claims rejected.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('lost.index') }}" class="btn btn-outline-danger btn-block">
                                <i class="fas fa-search"></i><br>
                                <small>Browse Lost Items</small>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('found.index') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-box"></i><br>
                                <small>Browse Found Items</small>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('items.report', 'lost') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-plus"></i><br>
                                <small>Report Lost Item</small>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('items.report', 'found') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-plus"></i><br>
                                <small>Report Found Item</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Activate tab based on URL hash
    if (window.location.hash) {
        $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
    }
    
    // Add hash to URL when tab is clicked
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        window.location.hash = e.target.getAttribute('href');
    });
});
</script>
@endsection