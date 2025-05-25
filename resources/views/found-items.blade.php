@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-weight-bold">Found Items</h2>
        <a href="{{ route('items.report', ['type' => 'found']) }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle mr-1"></i> Report Found Item
        </a>
    </div>

    <div class="mb-4">
        <form action="{{ route('found.index') }}" method="GET" class="form-inline">
            <div class="input-group w-100">
                <span class="input-group-text bg-white border-right-0">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-left-0" placeholder="Search Item..." value="{{ request('search') }}">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </div>
            </div>
        </form>
    </div>

    <div class="row">
        @if($foundItems->count() > 0)
            @foreach($foundItems as $item)
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <!-- Card Header with Avatar + Date -->
                        <div class="card-header bg-light d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-2" style="width: 35px; height: 35px;">
                                {{ strtoupper(substr($item->reporter_name ?? 'A', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-weight-bold small">{{ $item->reporter_name ?? 'Anonymous' }}</div>
                                <div class="text-muted small">{{ $item->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>

                        <!-- Card Image or Placeholder -->
                        @if($item->image_path)
                            <img src="{{ asset('storage/' . $item->image_path) }}" class="card-img-top" alt="{{ $item->title }}" style="height: 180px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex justify-content-center align-items-center" style="height: 180px;">
                                <i class="fas fa-box fa-3x text-muted"></i>
                            </div>
                        @endif

                        <!-- Card Body -->
                        <div class="card-body">
                            <h5 class="card-title mb-2">{{ $item->title }}</h5>
                            <p class="mb-1"><strong>Location:</strong> {{ $item->location }}</p>
                            <p class="mb-1"><strong>Category:</strong> {{ $item->category }}</p>
                            <p class="text-muted small">{{ Str::limit($item->description, 100) }}</p>
                        </div>

                        <!-- Footer with View Button -->
                        <div class="card-footer text-center bg-white border-top-0">
                            <a href="{{ route('items.show', $item) }}" class="btn btn-outline-primary btn-sm btn-block">View Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-md-12">
                <div class="alert alert-info text-center">
                    <h4>No found items available</h4>
                    <p>Be the first to report a found item!</p>
                </div>
            </div>
        @endif
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            {{ $foundItems->links() }}
        </div>
    </div>
</div>
@endsection
