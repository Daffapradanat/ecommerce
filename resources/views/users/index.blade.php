@extends('layouts')

@section('content')
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Administrator Management</h1>
        <a href="{{ route('users.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-user-plus fa-sm text-white-50"></i> Add New Administrator
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Administrator Account</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="align-middle text-center">
                                @if (filter_var($user->image, FILTER_VALIDATE_URL))
                                    <img src="{{ $user->image }}" alt="{{ $user->name }}" class="rounded-circle" width="50" height="50" style="object-fit: cover;">
                                @else
                                    @if ($user->image)
                                        <img src="{{ asset('storage/users/' . $user->image) }}" alt="{{ $user->name }}" class="rounded-circle" width="50" height="50" style="object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <span class="text-white" style="font-size: 24px;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="align-middle">{{ $user->name }}</td>
                            <td class="align-middle">{{ $user->email }}</td>
                            <td class="align-middle">{{ $user->role ?? 'Administrator' }}</td>
                            <td class="align-middle">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-info btn-sm me-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm me-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm me-0" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

@foreach($users as $user)
<!-- Delete Modal -->
<div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel{{ $user->id }}">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the administrator "{{ $user->name }}"?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('styles')

@endpush

@push('scripts')

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "language": {
                "lengthMenu": "Show _MENU_ entries per page",
                "zeroRecords": "No matching records found",
                "info": "Showing page _PAGE_ of _PAGES_",
                "infoEmpty": "No records available",
                "infoFiltered": "(filtered from _MAX_ total records)"
            }
        });
    });

        // Alert auto-close
        window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function(){
            $(this).remove();
        });
    }, 5000);
</script>
@endpush
