@extends('layouts')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
<<<<<<< HEAD
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="display-4">Buyer List</h1>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('buyer.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add New Buyer
            </a>
        </div>
    </div>

=======
    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h1 class="display-4">Buyer List</h1>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="{{ route('buyer.create') }}" class="btn btn-primary me-2">
                    <i class="fas fa-user-plus"></i> Add New Buyer
                </a>
                <a href="{{ route('buyer.export') }}" class="btn btn-info">
                    <i class="fas fa-file-export"></i> Export Buyers
                </a>
            </div>
        </div>
    </div>


>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Buyer Account</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="buyers-table">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Email</th>
<<<<<<< HEAD
=======
                            <th>Status</th>
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
                            <th>Actions</th>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
<<<<<<< HEAD
                Are you sure you want to delete this buyer?
=======
                Are you sure you want to mark this buyer as deleted?
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<<<<<<< HEAD
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css">
=======

>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>
@endpush

@push('scripts')
<<<<<<< HEAD
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>
=======

>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
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
<<<<<<< HEAD
=======
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
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // Handle delete modal
        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var buyerId = button.data('buyer-id');
            var form = $('#deleteForm');
<<<<<<< HEAD
            form.attr('action', '/buyers/' + buyerId);
=======
            form.attr('action', '/buyer/' + buyerId);
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
        });

        // Alert auto-close
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        }, 5000);
<<<<<<< HEAD
    });
=======

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
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
</script>
@endpush
