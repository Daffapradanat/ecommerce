@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h1 class="h3 mb-0">Edit Image</h1>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('image.update', $image->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Image Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $image->name }}" required>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('image.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </div>
                    </form>

                    <div class="mt-4">
                        <img src="{{ $image->url }}" alt="{{ $image->name }}" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
