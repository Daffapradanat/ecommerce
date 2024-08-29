@extends('layouts')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('administrator.create_role') }}</div>

                <div class="card-body">
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf

                        <div class="form-group row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('administrator.role_name') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('administrator.permissions') }}</label>
                            <div class="col-md-6">
                                @php
                                    $permissionGroups = [
                                        'products' => ['view', 'create', 'edit', 'delete'],
                                        'orders' => ['view', 'process', 'cancel'],
                                        'categories' => ['view', 'create', 'edit', 'delete'],
                                        'roles' => ['view', 'create', 'edit', 'delete'],
                                        'users' => ['view', 'create', 'edit', 'delete'],
                                        'buyers' => ['view', 'create', 'edit', 'delete']
                                    ];
                                @endphp

                                @foreach($permissionGroups as $group => $actions)
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input group-checkbox" type="checkbox" name="permissions[]" value="{{ $group }}" id="{{ $group }}">
                                            <label class="form-check-label" for="{{ $group }}">
                                                {{ __("administrator.$group") }}
                                            </label>
                                        </div>
                                        @foreach($actions as $action)
                                            <div class="form-check ml-4">
                                                <input class="form-check-input action-checkbox" type="checkbox" name="permissions[]" value="{{ $group }}.{{ $action }}" id="{{ $group }}.{{ $action }}" data-group="{{ $group }}">
                                                <label class="form-check-label" for="{{ $group }}.{{ $action }}">
                                                    {{ __("administrator.$action") }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('administrator.create') }} {{ __('administrator.role') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const groupCheckboxes = document.querySelectorAll('.group-checkbox');
    const actionCheckboxes = document.querySelectorAll('.action-checkbox');

    groupCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const group = this.id;
            const relatedActions = document.querySelectorAll(`.action-checkbox[data-group="${group}"]`);
            relatedActions.forEach(actionCheckbox => {
                actionCheckbox.checked = this.checked;
                actionCheckbox.disabled = !this.checked;
            });
        });

        // Initial state
        const group = checkbox.id;
        const relatedActions = document.querySelectorAll(`.action-checkbox[data-group="${group}"]`);
        relatedActions.forEach(actionCheckbox => {
            actionCheckbox.disabled = !checkbox.checked;
        });
    });

    actionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const group = this.dataset.group;
            const groupCheckbox = document.getElementById(group);
            const relatedActions = document.querySelectorAll(`.action-checkbox[data-group="${group}"]`);
            const allChecked = Array.from(relatedActions).every(cb => cb.checked);
            groupCheckbox.checked = allChecked;
        });
    });
});
</script>
@endpush
