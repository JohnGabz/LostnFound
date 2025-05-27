@extends('layouts.app')

@section('title', 'Lost Items')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Lost Items</h4>
                            <p class="text-muted mb-0">Help reunite people with their lost belongings</p>
                        </div>
                        <a href="{{ route('items.report', 'lost') }}" class="btn btn-danger">
                            <i class="fas fa-plus"></i> Report Lost Item
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('lost.index') }}" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                placeholder="Search by title, description, category, or location..." 
                                value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('lost.index') }}" class="btn btn-outline-danger">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                @endif
                            </div>
                        </div>
                        @if(request('search'))
                            <small class="text-muted">
                                Showing results for: <strong>"{{ request('search') }}"</strong>
                            </small>
                        @endif
                    </form>

                    <!-- Results Summary -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted">
                                @if(request('search'))
                                    Found {{ $lostItems->total() }} lost items matching your search
                                @else
                                    Showing {{ $lostItems->total() }} lost items
                                @endif
                            </span>
                        </div>
                        <div>
                            <a href="{{ route('found.index') }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-box"></i> Browse Found Items
                            </a>
                        </div>
                    </div>

                    <!-- Items Grid -->
                    @if($lostItems->count() > 0)
                        <div class="row">
                            @foreach($lostItems as $item)
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100 border-danger">
                                        <div class="position-relative">
                                            @if($item->image_path)
                                                <img src="{{ asset('storage/' . $item->image_path) }}" 
                                                     class="card-img-top" style="height: 200px; object-fit: cover;" 
                                                     alt="{{ $item->title }}">
                                            @else
                                                <div class="card-img-top bg-light d-flex justify-content-center align-items-center" 
                                                     style="height: 200px;">
                                                    <i class="fas fa-search fa-3x text-muted"></i>
                                                </div>
                                            @endif
                                            <div class="position-absolute" style="top: 10px; right: 10px;">
                                                <span class="badge badge-danger">LOST</span>
                                            </div>
                                        </div>
                                        
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title font-weight-bold">{{ $item->title }}</h6>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt"></i> {{ $item->location }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i> Lost on {{ $item->date_lost_found->format('M d, Y') }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-tag"></i> {{ $item->category }}
                                                </small>
                                            </div>
                                            
                                            <p class="card-text flex-grow-1">
                                                {{ Str::limit($item->description, 100) }}
                                            </p>
                                            
                                            <div class="mt-auto">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        by {{ $item->user->name ?? 'Anonymous' }}
                                                    </small>
                                                    <small class="text-muted">
                                                        {{ $item->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                
                                                <div class="mt-2">
                                                    <a href="{{ route('items.show', $item->item_id) }}" 
                                                       class="btn btn-outline-danger btn-sm btn-block">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                    
                                                    @if($item->user_id !== auth()->id())
                                                        <div class="mt-1">
                                                            <a href="{{ route('items.show', $item->item_id) }}#foundThisItemModal" 
                                                               class="btn btn-success btn-sm btn-block">
                                                                <i class="fas fa-check-circle"></i> I Found This!
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $lostItems->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            @if(request('search'))
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Lost Items Found</h5>
                                <p class="text-muted">
                                    We couldn't find any lost items matching "<strong>{{ request('search') }}</strong>".
                                </p>
                                <div>
                                    <a href="{{ route('lost.index') }}" class="btn btn-outline-secondary mr-2">
                                        <i class="fas fa-list"></i> View All Lost Items
                                    </a>
                                    <a href="{{ route('found.index') }}?search={{ request('search') }}" class="btn btn-outline-success">
                                        <i class="fas fa-search"></i> Search Found Items
                                    </a>
                                </div>
                            @else
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Lost Items</h5>
                                <p class="text-muted">There are no lost items reported yet.</p>
                                <a href="{{ route('items.report', 'lost') }}" class="btn btn-danger">
                                    <i class="fas fa-plus"></i> Report the First Lost Item
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Help Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle"></i> How it Works
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-search fa-2x text-danger mb-2"></i>
                            <h6>1. Browse Lost Items</h6>
                            <p class="text-muted small">Look through items that people have reported as lost.</p>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h6>2. Found Something?</h6>
                            <p class="text-muted small">Click "I Found This!" to notify the owner with your contact details.</p>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-handshake fa-2x text-primary mb-2"></i>
                            <h6>3. Reunite</h6>
                            <p class="text-muted small">The owner will contact you to arrange pickup and verify ownership.</p>
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
    // Auto-focus search input if there's a search parameter
    if('{{ request("search") }}') {
        $('input[name="search"]').focus();
    }
    
    // Handle "I Found This" clicks
    $('a[href*="#foundThisItemModal"]').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        var url = href.split('#')[0];
        window.location = url;
        
        // Small delay to ensure page loads before showing modal
        setTimeout(function() {
            $('#foundThisItemModal').modal('show');
        }, 500);
    });
});
</script>
@endsection