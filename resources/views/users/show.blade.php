@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h1 class="h3 mb-0">{{ __('administrator.administrator_details') }}</h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="text-center">
                                @if (filter_var($user->image, FILTER_VALIDATE_URL))
                                    <img src="{{ $user->image }}" alt="{{ $user->name }}" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    @if ($user->image)
                                        <img src="{{ asset('storage/users/' . $user->image) }}" alt="{{ $user->name }}" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                                            <span class="text-white" style="font-size: 48px;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h2 class="h4 mb-3">{{ $user->name }}</h2>
                            <dl class="row">
                                <dt class="col-sm-3">{{ __('administrator.email') }}</dt>
                                <dd class="col-sm-9">{{ $user->email }}</dd>

                                <dt class="col-sm-3">{{ __('administrator.role') }}</dt>
                                <dd class="col-sm-9">{{ $user->role ? $user->role->name : 'N/A' }}</dd>

                                <dt class="col-sm-3">{{ __('administrator.created_at') }}</dt>
                                <dd class="col-sm-9">{{ $user->created_at->format('d M Y H:i') }}</dd>

                                <dt class="col-sm-3">{{ __('administrator.updated_at') }}</dt>
                                <dd class="col-sm-9">{{ $user->updated_at->format('d M Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('administrator.back_to_list') }}
                        </a>
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> {{ __('administrator.edit') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
