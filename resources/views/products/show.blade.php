@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h1 class="h3 mb-0">Product Details</h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <img src="{{ $product->image->url }}" class="img-fluid rounded" alt="{{ $product->name }}">
                        </div>
                        <div class="col-md-8">
                            <h2 class="h4">{{ $product->name }}</h2>
                            <p class="text-muted">{{ $product->description }}</p>
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item"><strong>Price:</strong> ${{ number_format($product->price, 2) }}</li>
                                <li class="list-group-item"><strong>Stock:</strong> {{ $product->stock }}</li>
                                <li class="list-group-item"><strong>Category:</strong> {{ $product->category->name }}</li>
                            </ul>
                            <p class="text-muted small">
                                Created at: {{ $product->created_at->format('d M Y H:i') }}<br>
                                Last updated: {{ $product->updated_at->format('d M Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to List</a>
                        <div>
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning me-2">Edit</a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the product "{{ $product->name }}"?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var deleteModal = document.getElementById('deleteModal');
    new bootstrap.Modal(deleteModal);
});
</script>
@endpush
