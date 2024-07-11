@extends('layouts')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="display-4">Products</h1>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Product
            </a>
        </div>
    </div>

    @if(session('notification'))
    <div class="alert alert-{{ session('notification')['type'] }} alert-dismissible fade show" role="alert">
        {{ session('notification')['message'] }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="productsTable">
                    <thead class="table-light">
                        <tr>
                            <th><a href="#" class="sort" data-sort="image">Image</a></th>
                            <th><a href="#" class="sort" data-sort="name">Name</a></th>
                            <th><a href="#" class="sort" data-sort="price">Price</a></th>
                            <th><a href="#" class="sort" data-sort="stock">Stock</a></th>
                            <th><a href="#" class="sort" data-sort="category">Category</a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>
                                @if($product->image->isNotEmpty())
                                    <img src="{{ asset('storage/' . $product->image->first()->path) }}" alt="{{ $product->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <span class="text-muted">No image available</span>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>${{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->stock }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $product->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $product->id }}">Confirm Delete</h5>
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
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .sort {
        color: #333;
        text-decoration: none;
    }
    .sort:hover {
        color: #007bff;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('productsTable');
    const headers = table.querySelectorAll('th');
    const tableBody = table.querySelector('tbody');
    const rows = tableBody.querySelectorAll('tr');

    const directions = Array.from(headers).map(function(header) {
        return '';
    });

    const transform = function(index, content) {
        const type = headers[index].getAttribute('data-type');
        switch (type) {
            case 'number':
                return parseFloat(content);
            case 'string':
            default:
                return content;
        }
    };

    const sortColumn = function(index) {
        const direction = directions[index] || 'asc';
        const multiplier = (direction === 'asc') ? 1 : -1;
        const newRows = Array.from(rows);

        newRows.sort(function(rowA, rowB) {
            const cellA = rowA.querySelectorAll('td')[index].textContent;
            const cellB = rowB.querySelectorAll('td')[index].textContent;

            const a = transform(index, cellA);
            const b = transform(index, cellB);

            switch (true) {
                case a > b: return 1 * multiplier;
                case a < b: return -1 * multiplier;
                case a === b: return 0;
            }
        });

        [].forEach.call(rows, function(row) {
            tableBody.removeChild(row);
        });

        newRows.forEach(function(newRow) {
            tableBody.appendChild(newRow);
        });

        directions[index] = direction === 'asc' ? 'desc' : 'asc';
    };

    [].forEach.call(headers, function(header, index) {
        header.addEventListener('click', function() {
            sortColumn(index);
        });
    });

    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 3000);
    }
});
</script>
@endpush
