@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="font-weight-bold">My Claims</h2>
        </div>

        <ul class="nav nav-tabs mb-4" id="claimsTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab"
                    aria-controls="pending" aria-selected="true">
                    Pending <span class="badge badge-warning">{{ $pendingClaims->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="approved-tab" data-toggle="tab" href="#approved" role="tab" aria-controls="approved"
                    aria-selected="false">
                    Approved <span class="badge badge-success">{{ $approvedClaims->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="rejected-tab" data-toggle="tab" href="#rejected" role="tab" aria-controls="rejected"
                    aria-selected="false">
                    Rejected <span class="badge badge-danger">{{ $rejectedClaims->count() }}</span>
                </a>
            </li>
        </ul>

        <div class="tab-content" id="claimsTabsContent">
            {{-- Pending Claims Tab --}}
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                @if($pendingClaims->count() > 0)
                    <div class="row">
                        @foreach($pendingClaims as $claim)
                            <div class="col-md-3 mb-4">
                                <div class="card h-100 shadow-sm">
                                    {{-- Card Header with avatar letter and date --}}
                                    <div class="card-header bg-light d-flex align-items-center">
                                        <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-2"
                                            style="width: 35px; height: 35px;">
                                            {{ strtoupper(substr($claim->item->user->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-weight-bold small">{{ $claim->item->user->name ?? 'Unknown' }}</div>
                                            <div class="text-muted small">{{ $claim->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>

                                    {{-- Image --}}
                                    @if($claim->item->image_path)
                                        <img src="{{ asset('storage/' . $claim->item->image_path) }}" class="card-img-top"
                                            alt="{{ $claim->item->title }}" style="height: 180px; object-fit: cover;">
                                    @else
                                        <div class="card-img-top bg-light d-flex justify-content-center align-items-center"
                                            style="height: 180px;">
                                            <i class="fas fa-box fa-3x text-muted"></i>
                                        </div>
                                    @endif

                                    {{-- Card Body --}}
                                    <div class="card-body">
                                        <h5 class="card-title mb-2">{{ $claim->item->title }}</h5>
                                        <p class="mb-1"><strong>Status:</strong> <span class="badge badge-warning">Pending</span>
                                        </p>
                                        <p class="mb-1"><strong>Message:</strong> {{ Str::limit($claim->message, 100) }}</p>
                                    </div>

                                    {{-- Footer with View Button --}}
                                    <div class="card-footer text-center bg-white border-top-0">
                                        <a href="{{ route('items.show', $claim->item->item_id) }}"
                                            class="btn btn-outline-primary btn-sm btn-block">View Item</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        <h4>No pending claims found</h4>
                        <p>You haven't submitted any pending claims yet.</p>
                    </div>
                @endif
            </div>

            {{-- Approved Claims Tab --}}
            <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                @if($approvedClaims->count() > 0)
                    <div class="row">
                        @foreach($approvedClaims as $claim)
                            <div class="col-md-3 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-header bg-light d-flex align-items-center">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-2"
                                            style="width: 35px; height: 35px;">
                                            {{ strtoupper(substr($claim->item->user->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-weight-bold small">{{ $claim->item->user->name ?? 'Unknown' }}</div>
                                            <div class="text-muted small">{{ $claim->updated_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>

                                    @if($claim->item->image_path)
                                        <img src="{{ asset('storage/' . $claim->item->image_path) }}" class="card-img-top"
                                            alt="{{ $claim->item->title }}" style="height: 180px; object-fit: cover;">
                                    @else
                                        <div class="card-img-top bg-light d-flex justify-content-center align-items-center"
                                            style="height: 180px;">
                                            <i class="fas fa-box fa-3x text-muted"></i>
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <h5 class="card-title mb-2">{{ $claim->item->title }}</h5>
                                        <p class="mb-1"><strong>Status:</strong> <span class="badge badge-success">Approved</span>
                                        </p>
                                        <p class="mb-1"><strong>Contact:</strong> {{ $claim->item->user->email }}</p>
                                    </div>

                                    <div class="card-footer text-center bg-white border-top-0">
                                        <a href="{{ route('items.show', $claim->item->item_id) }}"
                                            class="btn btn-outline-primary btn-sm btn-block">View item</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        <h4>No approved claims found</h4>
                        <p>You don't have any approved claims yet.</p>
                    </div>
                @endif
            </div>

            {{-- Rejected Claims Tab --}}
            <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                @if($rejectedClaims->count() > 0)
                    <div class="row">
                        @foreach($rejectedClaims as $claim)
                            <div class="col-md-3 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-header bg-light d-flex align-items-center">
                                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mr-2"
                                            style="width: 35px; height: 35px;">
                                            {{ strtoupper(substr($claim->item->user->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-weight-bold small">{{ $claim->item->user->name ?? 'Unknown' }}</div>
                                            <div class="text-muted small">{{ $claim->updated_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>

                                    @if($claim->item->image_path)
                                        <img src="{{ asset('storage/' . $claim->item->image_path) }}" class="card-img-top"
                                            alt="{{ $claim->item->title }}" style="height: 180px; object-fit: cover;">
                                    @else
                                        <div class="card-img-top bg-light d-flex justify-content-center align-items-center"
                                            style="height: 180px;">
                                            <i class="fas fa-box fa-3x text-muted"></i>
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <h5 class="card-title mb-2">{{ $claim->item->title }}</h5>
                                        <p class="mb-1"><strong>Status:</strong> <span class="badge badge-danger">Rejected</span>
                                        </p>
                                        <p class="mb-1">Message: {{ Str::limit($claim->message, 100) }}</p>
                                    </div>

                                    <div class="card-footer text-center bg-white border-top-0">
                                        <a href="{{ route('items.show', $claim->item->item_id) }}"
                                            class="btn btn-outline-primary btn-sm btn-block">View Item</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        <h4>No rejected claims found</h4>
                        <p>You don't have any rejected claims.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
