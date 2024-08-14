@extends('layouts')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="display-4">Category List</h1>
        </div>
        <div class="col-md-6 text-md-end">
<<<<<<< HEAD
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Create New Category
=======
            <a href="{{ route('categories.create') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus"></i> Create New Category
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
            </a>
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import"></i> Import
            </button>
            <a href="{{ route('categories.export') }}" class="btn btn-info">
                <i class="fas fa-file-export"></i> Export
            </a>
            {{-- <a href="{{ route('categories.export') }}" class="btn btn-info" id="exportBtn">
                <i class="fas fa-file-export"></i> Export
            </a> --}}
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Categories</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover" id="categoriesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Product Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCategoryModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this category?
                <br><br>
                <strong class="text-danger">Warning:</strong> This action may affect associated products.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteCategoryForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<<<<<<< HEAD
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css">
=======

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('categories.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Choose Excel File</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls" required>
                    </div>
                    <p>Download the <a href="{{ route('categories.template') }}">Excel template</a> for import.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')

>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
<style>
    .table th, .table td {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<<<<<<< HEAD
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>
<script>
=======
<script>

// document.getElementById('exportBtn').addEventListener('click', function(event) {
//         event.preventDefault();
//         var iframe = document.createElement('iframe');
//         iframe.style.display = 'none';
//         iframe.src = '{{ route('categories.export') }}';
//         document.body.appendChild(iframe);

//         setTimeout(function() {
//             window.location.href = "{{ route('categories.index') }}";
//         }, 1000);
//     });

>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
$(document).ready(function() {
    $('#categoriesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('categories.index') }}",
        columns: [
            {data: 'name', name: 'name'},
            {data: 'slug', name: 'slug'},
            {data: 'products_count', name: 'products_count', searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    $('#categoriesTable').on('click', '.btn-danger', function(e) {
        e.preventDefault();
        var categoryId = $(this).data('id');
        var deleteUrl = "{{ route('categories.destroy', ':id') }}".replace(':id', categoryId);
        $('#deleteCategoryForm').attr('action', deleteUrl);
        $('#deleteCategoryModal').modal('show');
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
