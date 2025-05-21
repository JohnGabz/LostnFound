@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">Lost Items</h2>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('items.report', ['type' => 'lost']) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Report Lost Item
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('lost.index') }}" method="GET" class="form-inline">
                        <div class="input-group w-100">
                            <input type="text" name="search" class="form-control" placeholder="Search Item..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @if($lostItems->count() > 0)
            @foreach($lostItems as $item)
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ $item->title }}</span>
                                <small class="text-muted">{{ $item->created_at->format('M d, Y') }}</small>
                            </div>
                        </div>
                        
                        @if($item->image_path)
                            <img src="{{ asset('storage/' . $item->image_path) }}" class="card-img-top" alt="{{ $item->title }}" style="height: 180px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex justify-content-center align-items-center" style="height: 180px;">
                                <i class="fas fa-search fa-3x text-secondary"></i>
                            </div>
                        @endif
                        
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Location:</strong> {{ $item->location }}<br>
                                <strong>Date Lost:</strong> {{ $item->date_lost->format('M d, Y') }}
                            </p>
                            <p class="card-text text-truncate">{{ $item->description }}</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('items.show', $item->id) }}" class="btn btn-info btn-block">View Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-md-12">
                <div class="alert alert-info text-center">
                    <h4>No lost items reported</h4>
                    <p>Lucky you! Or be the first to report a missing item.</p>
                </div>
            </div>
        @endif
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            {{ $lostItems->links() }}
        </div>
    </div>
</div>
@endsection