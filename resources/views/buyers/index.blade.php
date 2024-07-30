@extends('layouts')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Buyer Account</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('buyer.index') }}" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Search by name or email" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($buyers as $buyer)
                            <tr>
                                <td>
                                    @if ($buyer->image)
                                        <img src="{{ asset('storage/buyers/' . $buyer->image) }}" alt="{{ $buyer->name }}" class="rounded-circle" width="50" height="50" style="object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px;">
                                            {{ strtoupper(substr($buyer->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $buyer->name }}</td>
                                <td class="align-middle">{{ $buyer->email }}</td>
                                <td class="align-middle">
                                    <td>
                                        <div class="d-flex justify-content-start align-items-center">
                                            <a href="{{ route('buyer.show', $buyer->id) }}" class="btn btn-info btn-sm me-2">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('buyer.destroy', $buyer->id) }}" method="POST" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm me-0" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $buyer->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($buyers as $buyer)
<!-- Delete Modal -->
<div class="modal fade" id="deleteModal{{ $buyer->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $buyer->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel{{ $buyer->id }}">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the buyer "{{ $buyer->name }}"?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('buyer.destroy', $buyer->id) }}" method="POST">
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
<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 767.98px) {
        .table-responsive .btn-group {
            display: flex;
            flex-direction: column;
        }
        .table-responsive .btn-group .btn {
            margin-bottom: 0.25rem;
            border-radius: 0.25rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>
<script>
    window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function(){
            $(this).remove();
        });
    }, 5000);
</script>
@endpush
