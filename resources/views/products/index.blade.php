@extends('layouts')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <h1 class="mt-4 mb-3 mb-md-0">{{ __('products.product_management') }}</h1>
        <div>
            @if(Auth::user()->can('products.create'))
                <a href="{{ route('products.create') }}" class="btn btn-primary me-2">
                    <i class="fas fa-plus"></i> {{ __('products.add_new_product') }}
                </a>
            @endif
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import"></i> {{ __('products.import') }}
            </button>
            <a href="{{ route('products.export') }}" class="btn btn-info">
                <i class="fas fa-file-export"></i> {{ __('products.export') }}
            </a>
        </div>
    </div>
</div>


@if(session('notification'))
<div class="alert alert-{{ session('notification')['type'] }} alert-dismissible fade show" role="alert">
    {{ session('notification')['message'] }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

    <!-- Products Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            {{ __('products.products_list') }}
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="productsTable">
                    <thead>
                        <tr>
                            <th>{{ __('products.images') }}</th>
                            <th>{{ __('products.name') }}</th>
                            <th>{{ __('products.price') }}</th>
                            <th>{{ __('products.stock') }}</th>
                            <th>{{ __('products.category') }}</th>
                            <th>{{ __('products.actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal template -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">{{ __('products.confirm_product_deletion') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ __('products.sure_delete_product') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('products.cancel') }}</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('products.delete_product') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">{{ __('products.import_products') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">{{ __('products.choose_excel_file') }}</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls" required>
                    </div>
                    <p>{{ __('products.download_the') }} <a href="{{ route('products.download.template') }}">{{ __('products.import_template') }}</a>.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('products.close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('products.import') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* ... (existing styles) ... */
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('products.index') }}",
            data: function (d) {
                d.category = $('#category').val();
                d.stock_status = $('#stock_status').val();
            }
        },
        columns: [
            {data: 'image', name: 'image', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'price', name: 'price'},
            {data: 'stock', name: 'stock'},
            {data: 'category', name: 'category.name'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    $('#category, #stock_status').change(function() {
        table.ajax.reload();
    });

    // Delete modal functionality
    $('#productsTable').on('click', '.btn-danger', function() {
        var productId = $(this).data('id');
        var deleteUrl = "{{ route('products.destroy', ':id') }}".replace(':id', productId);
        $('#deleteForm').attr('action', deleteUrl);
    });

    // Auto-hide alert
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    }
});
</script>
@endpush
