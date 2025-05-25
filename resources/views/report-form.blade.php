@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Report {{ ucfirst($type) }} Item</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        
                        <div class="form-group">
                            <label for="title">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                            @error('title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category <span class="text-danger">*</span></label>
                            <select name="category" id="category" class="form-control @error('category') is-invalid @enderror" required>
                                <option value="">-- Select Category --</option>
                                <option value="Electronics" {{ old('category') == 'Electronics' ? 'selected' : '' }}>Electronics</option>
                                <option value="ID/Documents" {{ old('category') == 'ID/Documents' ? 'selected' : '' }}>ID/Documents</option>
                                <option value="Keys" {{ old('category') == 'Keys' ? 'selected' : '' }}>Keys</option>
                                <option value="Clothing" {{ old('category') == 'Clothing' ? 'selected' : '' }}>Clothing</option>
                                <option value="Jewelry" {{ old('category') == 'Jewelry' ? 'selected' : '' }}>Jewelry</option>
                                <option value="Accessories" {{ old('category') == 'Accessories' ? 'selected' : '' }}>Accessories</option>
                                <option value="Books" {{ old('category') == 'Books' ? 'selected' : '' }}>Books</option>
                                <option value="Others" {{ old('category') == 'Others' ? 'selected' : '' }}>Others</option>
                            </select>
                            @error('category')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}" required>
                            @error('location')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Date {{ $type == 'lost' ? 'Lost' : 'Found' }} <span class="text-danger">*</span></label>
                            <input type="date" name="date_lost_found" id="date" class="form-control @error('date_lost_found') is-invalid @enderror" value="{{ old('date_lost_found') ?? date('Y-m-d') }}" required>
                                @error('date_lost_found')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                Please provide detailed description of the item (color, brand, identifiable marks, etc.)
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Upload Photo</label>
                            <div class="custom-file">
                                <input type="file" name="image" id="image" class="custom-file-input @error('image') is-invalid @enderror" accept="image/*">
                                <label class="custom-file-label" for="image">Choose file...</label>
                                @error('image')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                Adding a photo increases the chances of identifying the item (max 2MB)
                            </small>
                        </div>
                        
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Report
                            </button>
                            <a href="{{ $type == 'lost' ? route('lost.index') : route('found.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
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
    // Display the name of the selected file
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var label = e.target.nextElementSibling;
        label.innerHTML = fileName;
    });
</script>
@endsection