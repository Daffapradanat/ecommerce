@extends('layouts')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="display-4">Image Gallery</h1>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('image.create') }}" class="btn btn-primary">
                <i class="fas fa-upload"></i> Upload New Image
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @forelse($images as $image)
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="{{ $image->url }}" class="card-img-top" alt="{{ $image->name }}" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">{{ Str::limit($image->name, 20) }}</h5>
                        <p class="card-text">
                            <small class="text-muted">
                                Size: {{ number_format($image->file_size / 1024, 2) }} KB<br>
                                Type: {{ $image->mime_type }}
                            </small>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('image.show', $image->id) }}" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ route('image.edit', $image->id) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('image.destroy', $image->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">No images found.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
