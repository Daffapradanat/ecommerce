@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h1 class="h3 mb-0">Create New Buyer</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('buyer.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        <div class="mb-3">
                            <label for="image_type" class="form-label">Profile Image Type</label>
                            <select class="form-select @error('image_type') is-invalid @enderror" id="image_type" name="image_type">
                                <option value="">Select image type</option>
                                <option value="upload" {{ old('image_type') == 'upload' ? 'selected' : '' }}>Upload Image</option>
                                <option value="url" {{ old('image_type') == 'url' ? 'selected' : '' }}>Image URL</option>
                            </select>
                            @error('image_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3" id="image_upload" style="display: none;">
                            <label for="image" class="form-label">Upload Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3" id="image_url" style="display: none;">
                            <label for="image_url_input" class="form-label">Image URL</label>
                            <input type="url" class="form-control @error('image_url') is-invalid @enderror" id="image_url_input" name="image_url" placeholder="https://example.com/image.jpg" value="{{ old('image_url') }}">
                            @error('image_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('buyer.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Create Buyer</button>
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
