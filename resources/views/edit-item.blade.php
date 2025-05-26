@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit {{ ucfirst($item->type) }} Item</h5>
                        <span
                            class="badge badge-{{ $item->status == 'claimed' ? 'success' : ($item->type == 'lost' ? 'danger' : 'primary') }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('items.update', $item) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" name="title" id="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $item->title) }}" required>
                                @error('title')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="category">Category</label>
                                <input type="text" name="category" id="category"
                                    class="form-control @error('category') is-invalid @enderror"
                                    value="{{ old('category', $item->category) }}" required>
                                @error('category')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" name="location" id="location"
                                    class="form-control @error('location') is-invalid @enderror"
                                    value="{{ old('location', $item->location) }}" required>
                                @error('location')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description"
                                    class="form-control @error('description') is-invalid @enderror"
                                    rows="4">{{ old('description', $item->description) }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            @if($item->type === 'lost')
                                <div class="form-group">
                                    <label for="date_lost">Date Lost</label>
                                    <input type="date" name="date_lost" id="date_lost"
                                        class="form-control @error('date_lost') is-invalid @enderror"
                                        value="{{ old('date_lost', optional($item->date_lost)->format('Y-m-d')) }}">
                                    @error('date_lost')
                                    @enderror
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="image">Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('image') is-invalid @enderror"
                                        id="image" name="image">
                                    <label class="custom-file-label" for="image">Choose image...</label>
                                </div>
                                @error('image')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror

                                @if($item->image_path)
                                    <div class="mt-3">
                                        <p>Current Image:</p>
                                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="Current Image"
                                            class="img-fluid rounded" style="max-height: 250px;">
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route($item->type . '.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Item
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelector('.custom-file-input')?.addEventListener('change', function (e) {
            var fileName = e.target.files[0]?.name;
            if (fileName) {
                e.target.nextElementSibling.innerHTML = fileName;
            }
        });
    </script>
@endsection