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
                            <label class="form-label">Profile Image</label>
                            <div id="drop-area" class="border rounded p-4 text-center position-relative"
                                style="background-color: #f8f9fa; border: 2px dashed #ced4da; transition: all 0.3s ease;">
                                <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: #6c757d;"></i>
                                <p class="mb-2">Drag and drop an image here, or click to select a file</p>
                                <small class="text-muted">Supports: JPG, JPEG, PNG, GIF up to 2MB</small>
                                <input type="file" id="fileElem" name="image" accept="image/*" style="display: none;" onchange="handleFiles(this.files)">
                                <div id="preview-container" class="mt-3 d-flex justify-content-center align-items-center"></div>
                            </div>
                            @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-3">
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
    let dropArea = document.getElementById('drop-area');
    let fileElem = document.getElementById('fileElem');
    let previewContainer = document.getElementById('preview-container');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropArea.style.backgroundColor = '#e9ecef';
        dropArea.style.borderColor = '#6c757d';
    }

    function unhighlight(e) {
        dropArea.style.backgroundColor = '#f8f9fa';
        dropArea.style.borderColor = '#ced4da';
    }

    dropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        let dt = e.dataTransfer;
        let files = dt.files;
        handleFiles(files);
    }

    dropArea.addEventListener('click', () => fileElem.click());

    function handleFiles(files) {
        if (files.length > 0) {
            let file = files[0];
            if (file.type.startsWith('image/')) {
                if (file.size <= 2 * 1024 * 1024) { // 2MB limit
                    previewFile(file);
                    fileElem.files = files; // Update the file input
                } else {
                    alert('File size exceeds 2MB limit');
                }
            } else {
                alert('Please upload an image file');
            }
        }
    }

    function previewFile(file) {
        let reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onloadend = function() {
            let img = document.createElement('img');
            img.src = reader.result;
            img.className = 'img-thumbnail mt-2';
            img.style.maxWidth = '200px';
            img.style.maxHeight = '200px';
            previewContainer.innerHTML = '';
            previewContainer.appendChild(img);

            // Add file name
            let fileName = document.createElement('p');
            fileName.textContent = file.name;
            fileName.className = 'mt-2 mb-0';
            previewContainer.appendChild(fileName);

            // Add remove button
            let removeBtn = document.createElement('button');
            removeBtn.textContent = 'Remove';
            removeBtn.className = 'btn btn-sm btn-danger mt-2';
            removeBtn.onclick = function() {
                previewContainer.innerHTML = '';
                fileElem.value = ''; // Clear the file input
            };
            previewContainer.appendChild(removeBtn);
        }
    }
</script>
@endpush
