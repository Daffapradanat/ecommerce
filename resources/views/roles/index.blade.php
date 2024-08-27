@extends('layouts')

@section('content')
<div class="container">
    <h1>{{ __('administrator.roles') }}</h1>
    <a href="{{ route('roles.create') }}" class="btn btn-primary mb-3">{{ __('administrator.create_new_role') }}</a>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered" id="roles-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ __('administrator.role_name') }}</th>
                        <th>{{ __('administrator.action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#roles-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('roles.index') }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush
