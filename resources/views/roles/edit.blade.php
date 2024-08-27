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
                        <div class="form-group row">
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
                                    $permissions = ['products', 'orders', 'categories', 'roles', 'users', 'buyers',];
                                @endphp
                                @foreach($permissions as $permission)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="{{ $permission }}"
                                            {{ isset($role) && in_array($permission, $role->permissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $permission }}">
                                            {{ ucfirst($permission) }}
                                        </label>
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
