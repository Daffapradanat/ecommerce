@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0">{{ __('buyer.edit_buyer') }}</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('buyer.update', $buyer->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('buyer.name') }}</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $buyer->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('buyer.email') }}</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $buyer->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('buyer.profile_image') }}</label>
                            <div id="drop-area" class="border rounded p-4 text-center position-relative" style="background-color: #f8f9fa; border: 2px dashed #ced4da !important; transition: all 0.3s ease;">
                                <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: #6c757d;"></i>
                                <p class="mb-2">{{ __('buyer.image_upload_instruction') }}</p>
                                <small class="text-muted">{{ __('buyer.image_support_info') }}</small>
                                <input type="file" id="fileElem" name="image" accept="image/*" style="display:none" onchange="handleFiles(this.files)">
                                <div id="preview-container" class="mt-3 d-flex justify-content-center align-items-center">
                                    @if($buyer->image)
                                        <img src="{{ asset('storage/buyers/' . $buyer->image) }}" alt="{{ $buyer->name }}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    @endif
                                </div>
                            </div>
                            @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('buyer.index') }}" class="btn btn-secondary">{{ __('buyer.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('buyer.update_buyer') }}</button>
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
