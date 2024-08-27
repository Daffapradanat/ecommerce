@extends('layouts')

@section('content')
<div class="container">
    <h1>{{ isset($role) ? __('edit_role') : __('create_role') }}</h1>

    <form action="{{ isset($role) ? route('roles.update', $role) : route('roles.store') }}" method="POST">
        @csrf
        @if(isset($role))
            @method('PUT')
        @endif
        <div class="form-group">
            <label for="name">{{ __('administrator.role_name') }}</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name ?? '') }}" required>
            @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label>{{ __('permissions') }}</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permissions[]" value="products" id="products" {{ isset($role) && in_array('products', $role->permissions ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="products">{{ __('administrator.products') }}</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permissions[]" value="orders" id="orders" {{ isset($role) && in_array('orders', $role->permissions ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="orders">{{ __('administrator.orders') }}</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permissions[]" value="categories" id="categories" {{ isset($role) && in_array('categories', $role->permissions ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="categories">{{ __('administrator.categories') }}</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permissions[]" value="roles" id="roles" {{ isset($role) && in_array('roles', $role->permissions ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="roles">{{ __('administrator.roles') }}</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permissions[]" value="users" id="users" {{ isset($role) && in_array('users', $role->permissions ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="users">{{ __('administrator.users') }}</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permissions[]" value="buyer" id="buyer" {{ isset($role) && in_array('buyer', $role->permissions ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="buyer">{{ __('administrator.buyer') }}</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ isset($role) ? __('administrator.update') : __('administrator.create') }} {{ __('administrator.roles') }}</button>
    </form>
</div>
@endsection
