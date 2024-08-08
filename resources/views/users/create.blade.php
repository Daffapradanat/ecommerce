@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h1 class="h3 mb-0">Create New Administrator</h1>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
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
                            @if($errors->has('image_url'))
                                <div id="server-error-message" class="alert alert-danger mt-2">
                                    {{ $errors->first('image_url') }}
                                </div>
                            @endif
                            <small class="form-text text-muted">URL harus langsung mengarah ke file gambar (jpg, jpeg, png, atau gif).</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Create User</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        var serverErrorMessage = document.getElementById('server-error-message');
        if (serverErrorMessage) {
            setTimeout(function() {
                location.reload();
            }, 2000);
        }

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

        document.getElementById('image_url_input').addEventListener('blur', function() {
            var url = this.value;
            var validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            var extension = url.split('.').pop().toLowerCase();

            if (url && !validExtensions.includes(extension)) {
                this.classList.add('is-invalid');
                var feedbackElement = this.nextElementSibling;
                if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                    feedbackElement = document.createElement('div');
                    feedbackElement.classList.add('invalid-feedback');
                    this.parentNode.insertBefore(feedbackElement, this.nextSibling);
                }
                feedbackElement.textContent = 'URL harus langsung mengarah ke file gambar (jpg, jpeg, png, atau gif).';
            } else {
                this.classList.remove('is-invalid');
                var feedbackElement = this.nextElementSibling;
                if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                    feedbackElement.remove();
                }
            }
        });
    });
</script>
@endpush
