@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0">Edit Buyer</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('buyer.update', $buyer->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $buyer->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $buyer->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Profile Image</label>
                            @if($buyer->image)
                                <div class="mb-2">
                                    @if (filter_var($buyer->image, FILTER_VALIDATE_URL))
                                        <img src="{{ $buyer->image }}" alt="{{ $buyer->name }}" class="img-thumbnail" style="max-width: 200px;">
                                    @else
                                        <img src="{{ asset('storage/buyers/' . $buyer->image) }}" alt="{{ $buyer->name }}" class="img-thumbnail" style="max-width: 200px;">
                                    @endif
                                </div>
                            @else
                                <p>No image currently set</p>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="image_type" class="form-label">Update Profile Image</label>
                            <select class="form-select @error('image_type') is-invalid @enderror" id="image_type" name="image_type">
                                <option value="keep" {{ old('image_type') == 'keep' ? 'selected' : '' }}>Select image type</option>
                                {{-- <option value="">Select image type</option> --}}
                                <option value="upload" {{ old('image_type') == 'upload' ? 'selected' : '' }}>Upload New Image</option>
                                <option value="url" {{ old('image_type') == 'url' ? 'selected' : '' }}>New Image URL</option>
                                <option value="keep" {{ old('image_type') == 'keep' ? 'selected' : '' }}>Keep Current Image</option>
                            </select>
                            @error('image_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3" id="image_upload" style="display: none;">
                            <label for="image" class="form-label">Upload New Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3" id="image_url" style="display: none;">
                            <label for="image_url_input" class="form-label">New Image URL</label>
                            <input type="url" class="form-control @error('image_url') is-invalid @enderror" id="image_url_input" name="image_url" placeholder="https://example.com/image.jpg" value="{{ old('image_url') }}">
                            @error('image_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('buyer.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Buyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('image_type').addEventListener('change', function() {
        var uploadDiv = document.getElementById('image_upload');
        var urlDiv = document.getElementById('image_url');
        if (this.value === 'upload') {
            uploadDiv.style.display = 'block';
            urlDiv.style.display = 'none';
        } else if (this.value === 'url') {
            uploadDiv.style.display = 'none';
            urlDiv.style.display = 'block';
        } else {
            uploadDiv.style.display = 'none';
            urlDiv.style.display = 'none';
        }
    });
</script>
@endpush
