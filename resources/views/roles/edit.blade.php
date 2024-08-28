@extends('layouts')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ isset($role) ? __('administrator.edit_role') : __('administrator.create_role') }}</div>

                <div class="card-body">
                    <form action="{{ isset($role) ? route('roles.update', $role) : route('roles.store') }}" method="POST">
                        @csrf
                        @if(isset($role))
                            @method('PUT')
                        @endif

                        <div class="form-group row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('administrator.role_name') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name ?? '') }}" required autofocus>
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
                                        'products' => ['create', 'edit', 'delete'],
                                        'orders' => [],
                                        'categories' => ['create', 'edit', 'delete'],
                                        'roles' => ['create','edit','delete'],
                                        'users' => ['create', 'edit', 'delete'],
                                        'buyers' => ['create', 'edit', 'delete']
                                    ];
                                @endphp

                                @foreach($permissionGroups as $group => $actions)
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $group }}" id="{{ $group }}"
                                                {{ isset($role) && in_array($group, $role->permissions ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $group }}">
                                                {{ __("administrator.$group") }}
                                            </label>
                                        </div>
                                        @foreach($actions as $action)
                                            <div class="form-check ml-4">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $group }}.{{ $action }}" id="{{ $group }}.{{ $action }}"
                                                    {{ isset($role) && in_array("$group.$action", $role->permissions ?? []) ? 'checked' : '' }}>
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
                                    {{ isset($role) ? __('administrator.update') : __('administrator.create') }} {{ __('administrator.roles') }}
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
    const groupCheckboxes = document.querySelectorAll('input[type="checkbox"]:not([id*="."])');
    groupCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const group = this.id;
            const subCheckboxes = document.querySelectorAll(`input[type="checkbox"][id^="${group}."]`);
            subCheckboxes.forEach(subCheckbox => {
                subCheckbox.checked = this.checked;
                subCheckbox.disabled = !this.checked;
            });
        });

        // Initial state
        const group = checkbox.id;
        const subCheckboxes = document.querySelectorAll(`input[type="checkbox"][id^="${group}."]`);
        subCheckboxes.forEach(subCheckbox => {
            subCheckbox.disabled = !checkbox.checked;
        });
    });
});
</script>
@endpush