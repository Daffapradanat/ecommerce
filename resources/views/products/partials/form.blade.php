<div class="form-group">
    <label for="name">Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description', $product->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="price">Price</label>
    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price ?? '') }}" required>
    @error('price')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="stock">Stock</label>
    <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock ?? '') }}" required>
    @error('stock')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="category_id">Category</label>
    <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
        <option value="">Select a category</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ (old('category_id', $product->category_id ?? '') == $category->id) ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('category_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="images">Images</label>
    <div id="drop-area" class="drop-area">
        <p>Drag and drop images here or click to select files</p>
        <input type="file" id="fileElem" name="images[]" multiple accept="image/*" style="display:none" onchange="handleFiles(this.files)">
    </div>
    <div id="gallery" class="image-preview"></div>
    @error('images')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@if(isset($product) && $product->images)
    <div class="form-group">
        <label>Current Images</label>
        <div id="current-images" class="row">
            @foreach($product->images as $image)
                <div class="col-md-3 mb-3">
                    <div class="image-container">
                        <img src="{{ asset('storage/' . $image) }}" alt="Product Image" class="img-thumbnail">
                        <div class="image-overlay">
                            <input type="checkbox" name="remove_images[]" value="{{ $image }}" id="remove_image_{{ $loop->index }}">
                            <label for="remove_image_{{ $loop->index }}">Remove</label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<style>
    .drop-area {
        border: 2px dashed #ccc;
        border-radius: 20px;
        width: 100%;
        padding: 20px;
        text-align: center;
        background-color: #f8f8f8;
        cursor: pointer;
    }
    .drop-area.highlight {
        border-color: purple;
        background-color: #f0f0f0;
    }
    .image-preview {
        display: flex;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    .image-preview .image-container {
        position: relative;
        margin-right: 10px;
        margin-bottom: 10px;
    }
    .image-preview img {
        max-width: 150px;
        max-height: 150px;
        object-fit: cover;
    }
    .image-container {
        position: relative;
    }
    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .image-container:hover .image-overlay {
        opacity: 1;
    }
    .image-overlay label {
        color: white;
        cursor: pointer;
    }
    .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: rgba(255, 255, 255, 0.7);
        border-radius: 50%;
        padding: 5px;
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
    }
</style>

<script>
    let dropArea = document.getElementById('drop-area');
    let fileElem = document.getElementById('fileElem');
    let gallery = document.getElementById('gallery');

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
        dropArea.classList.add('highlight');
    }

    function unhighlight(e) {
        dropArea.classList.remove('highlight');
    }

    dropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        let dt = e.dataTransfer;
        let files = dt.files;
        handleFiles(files);
    }

    dropArea.addEventListener('click', () => fileElem.click());

    function handleFiles(files) {
        ([...files]).forEach(uploadFile);
    }

    function uploadFile(file) {
        let container = document.createElement('div');
        container.className = 'image-container';

        let img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.onload = function() {
            URL.revokeObjectURL(this.src);
        }
        container.appendChild(img);

        let removeBtn = document.createElement('span');
        removeBtn.innerHTML = '&times;';
        removeBtn.className = 'remove-image';
        removeBtn.onclick = function() {
            gallery.removeChild(container);
        }
        container.appendChild(removeBtn);

        gallery.appendChild(container);

    }
</script>
