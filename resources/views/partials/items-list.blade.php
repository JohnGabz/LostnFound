@if($items->count() > 0)
    <div class="row">
        @foreach($items as $item)
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                    @if($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" class="card-img-top"
                             alt="{{ $item->title }}" style="height: 180px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-light d-flex justify-content-center align-items-center"
                             style="height: 180px;">
                            <i class="fas fa-box fa-3x text-muted"></i>
                        </div>
                    @endif

                    <div class="card-body">
                        <h5 class="card-title mb-2">{{ $item->title }}</h5>
                        <p class="mb-1"><strong>Category:</strong> {{ $item->category }}</p>
                        <p class="mb-1"><strong>Location:</strong> {{ $item->location }}</p>
                        <p class="mb-1">
                            <strong>Status:</strong>
                            <span class="badge badge-{{ $item->status === 'claimed' ? 'success' : $badge }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </p>
                        <p class="text-muted small">Reported on {{ $item->created_at->format('M d, Y') }}</p>
                    </div>

                    <div class="card-footer text-center bg-white border-top-0">
                        <a href="{{ route('items.show', $item->item_id) }}" class="btn btn-outline-primary btn-sm btn-block">
                            <i class="fas fa-eye"></i> View Item
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info text-center">
        <h4>No items found</h4>
        <p>This category has no items reported yet.</p>
    </div>
@endif
