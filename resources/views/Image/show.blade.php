@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">Image Details</h1>
                    <a href="{{ route('image.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Gallery
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <img src="{{ asset('storage/' . $image->file_path) }}" alt="{{ $image->name }}" class="img-fluid rounded">
                    </div>

                    <dl class="row">
                        <dt class="col-sm-3">Name:</dt>
                        <dd class="col-sm-9">{{ $image->name }}</dd>

                        <dt class="col-sm-3">File Name:</dt>
                        <dd class="col-sm-9">{{ $image->file_name }}</dd>

                        <dt class="col-sm-3">MIME Type:</dt>
                        <dd class="col-sm-9">{{ $image->mime_type }}</dd>

                        <dt class="col-sm-3">File Size:</dt>
                        <dd class="col-sm-9">{{ number_format($image->file_size / 1024, 2) }} KB</dd>

                        <dt class="col-sm-3">Uploaded At:</dt>
                        <dd class="col-sm-9">{{ $image->created_at->format('F d, Y H:i:s') }}</dd>
                    </dl>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('image.edit', $image->id) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('image.destroy', $image->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this image?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
