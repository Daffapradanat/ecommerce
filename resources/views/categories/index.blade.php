@extends('layouts')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="display-4">{{ __('category_list.category_list') }}</h1>
        </div>
        <div class="col-md-6 text-md-end">
            @if(Auth::user()->can('categories.create'))
                <a href="{{ route('categories.create') }}" class="btn btn-primary me-2">
                    <i class="fas fa-plus"></i> {{ __('category_list.create_new_category') }}
                </a>
            @endif
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import"></i> {{ __('category_list.import') }}
            </button>
            <a href="{{ route('categories.export') }}" class="btn btn-info">
                <i class="fas fa-file-export"></i> {{ __('category_list.export') }}
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('category_list.categories') }}</h6>
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
                            <th>{{ __('category_list.name') }}</th>
                            <th>{{ __('category_list.slug') }}</th>
                            <th>{{ __('category_list.description') }}</th>
                            <th>{{ __('category_list.product_count') }}</th>
                            <th>{{ __('category_list.actions') }}</th>
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
                <h5 class="modal-title" id="deleteCategoryModalLabel">{{ __('category_list.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ __('category_list.sure_delete_category') }}
                <br><br>
                <strong class="text-danger">{{ __('category_list.warning') }}:</strong> {{ __('category_list.delete_affect_products') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('category_list.cancel') }}</button>
                <form id="deleteCategoryForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('category_list.delete') }}</button>
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
                <h5 class="modal-title" id="importModalLabel">{{ __('category_list.import_categories') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('categories.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">{{ __('category_list.choose_excel_file') }}</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls" required>
                    </div>
                    <p>{{ __('category_list.download_the') }} <a href="{{ route('categories.template') }}">{{ __('category_list.excel_template') }}</a> {{ __('category_list.for_import') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('category_list.close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('category_list.import') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')

<style>
    .table th, .table td {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script>

$(document).ready(function() {
    $('#categoriesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('categories.index') }}",
        columns: [
            {data: 'name', name: 'name'},
            {data: 'slug', name: 'slug'},
            {data: 'description', name: 'description'},
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
