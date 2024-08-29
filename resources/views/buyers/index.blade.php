@extends('layouts')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h1 class="display-4">{{ __('buyer.buyer_list') }}</h1>
            </div>
            <div class="col-md-6 text-md-end">
                @if(Auth::user()->can('buyers.create'))
                    <a href="{{ route('buyer.create') }}" class="btn btn-primary me-2">
                        <i class="fas fa-user-plus"></i> {{ __('buyer.add_new_buyer') }}
                    </a>
                @endif
                <a href="{{ route('buyer.export') }}" class="btn btn-info">
                    <i class="fas fa-file-export"></i> {{ __('buyer.export_buyers') }}
                </a>
            </div>
        </div>
    </div>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('buyer.buyer_account') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="buyers-table">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('buyer.image') }}</th>
                            <th>{{ __('buyer.name') }}</th>
                            <th>{{ __('buyer.email') }}</th>
                            <th>{{ __('buyer.status') }}</th>
                            <th>{{ __('buyer.actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">{{ __('buyer.confirm_deletion') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ __('buyer.delete_confirmation') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('buyer.cancel') }}</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('buyer.delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')

<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>
@endpush

@push('scripts')

<script>
    $(document).ready(function() {
        $('#buyers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('buyer.index') }}",
            columns: [
                { data: 'image', name: 'image', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        if (data === 'active') {
                            return '<span class="badge bg-success">Active</span>';
                        } else if (data === 'deleted') {
                            return '<span class="badge bg-danger text-dark">Deleted</span>';
                        }
                        return data;
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // Handle delete modal
        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var buyerId = button.data('buyer-id');
            var form = $('#deleteForm');
            form.attr('action', '/buyer/' + buyerId);
        });

        // Alert auto-close
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        }, 5000);

        // Disable delete button for deleted buyers
        $('#buyers-table').on('draw.dt', function() {
        $('.delete-btn').each(function() {
            var tr = $(this).closest('tr');
            var row = $('#buyers-table').DataTable().row(tr);
            if (row.data().status === 'deleted') {
                $(this).prop('disabled', true);
            }
        });
    });
});
</script>
@endpush
