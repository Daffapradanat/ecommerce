@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-{{ isset($user) ? 'primary' : 'success' }} text-white">
                    <h1 class="h3 mb-0">{{ isset($user) ? __('administrator.edit_administrator') : __('administrator.create_new_administrator') }}</h1>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($user))
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('administrator.name') }}</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('administrator.email') }}</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" {{ isset($user) ? 'readonly' : 'required' }}>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @if(!isset($user))
                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('administrator.password') }}</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">{{ __('administrator.confirm_password') }}</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label for="role" class="form-label">{{ __('administrator.role') }}</label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">{{ __('administrator.select_role') }}</option>
                                <option value="admin" {{ (old('role', $user->role ?? '') == 'admin') ? 'selected' : '' }}>Admin</option>
                                <option value="manager" {{ (old('role', $user->role ?? '') == 'manager') ? 'selected' : '' }}>Manager</option>
                                <option value="editor" {{ (old('role', $user->role ?? '') == 'editor') ? 'selected' : '' }}>Editor</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('administrator.profile_image') }}</label>
                            <div id="drop-area" class="border rounded p-4 text-center position-relative"
                                style="background-color: #f8f9fa; border: 2px dashed #ced4da; transition: all 0.3s ease;">
                                <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: #6c757d;"></i>
                                <p class="mb-2">{{ __('administrator.image_upload_instruction') }}</p>
                                <small class="text-muted">{{ __('administrator.image_support_info') }}</small>
                                <input type="file" id="fileElem" name="image" accept="image/*" style="display: none;" onchange="handleFiles(this.files)">
                                <div id="preview-container" class="mt-3 d-flex justify-content-center align-items-center">
                                    @if(isset($user) && $user->image)
                                        <img src="{{ asset('storage/users/' . $user->image) }}" alt="{{ $user->name }}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    @endif
                                </div>
                            </div>
                            @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('administrator.cancel') }}</a>
                            <button type="submit" class="btn btn-{{ isset($user) ? 'primary' : 'success' }}">
                                {{ isset($user) ? __('administrator.update_user') : __('administrator.create_user') }}
                            </button>
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
