@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0">{{ __('buyer.buyer_profile') }}</h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4 mb-md-0">
                            <div class="text-center">
                                @if($buyer->image)
                                    @if(filter_var($buyer->image, FILTER_VALIDATE_URL))
                                        <img src="{{ $buyer->image }}" alt="{{ $buyer->name }}" class="img-fluid rounded-circle shadow" style="width: 200px; height: 200px; object-fit: cover;">
                                    @else
                                        <img src="{{ asset('storage/buyers/' . $buyer->image) }}" alt="{{ $buyer->name }}" class="img-fluid rounded-circle shadow" style="width: 200px; height: 200px; object-fit: cover;">
                                    @endif
                                @else
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto shadow" style="width: 200px; height: 200px;">
                                        <span class="text-white" style="font-size: 72px;">{{ strtoupper(substr($buyer->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h2 class="h3 mb-3 d-flex justify-content-between align-items-center">
                                {{ $buyer->name }}
                                @if($buyer->status === 'active')
                                    <span class="badge bg-success">{{ __('buyer.active') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('buyer.deleted') }}</span>
                                @endif
                            </h2>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h3 class="h5 mb-3">{{ __('buyer.contact_information') }}</h3>
                                        <p><i class="fas fa-envelope me-2"></i> {{ $buyer->email }}</p>
                                        <p><i class="fas fa-phone me-2"></i> {{ $buyer->phone ?? 'Not provided' }}</p>
                                    </div>
                                </div>
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="h5 mb-3">{{ __('buyer.account_details') }}</h3>
                                    <p><strong>{{ __('buyer.member_since') }}:</strong> {{ $buyer->created_at->format('d M Y') }}</p>
                                    <p><strong>{{ __('buyer.last_updated') }}:</strong> {{ $buyer->updated_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('buyer.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> {{ __('buyer.back_to_list') }}
                        </a>

                        @if($buyer->status === 'deleted')
                            @if(Auth::user()->can('buyers.edit'))
                                <form action="{{ route('buyer.restore', $buyer->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-undo me-1"></i> {{ __('buyer.restore') }}
                                    </button>
                                </form>
                            @endif
                        @else
                            <div>
                                @if(Auth::user()->can('buyers.edit'))
                                    <a href="{{ route('buyer.edit', $buyer->id) }}" class="btn btn-warning me-2">
                                        <i class="fas fa-edit me-1"></i>
                                    </a>
                                @endif

                                @if(Auth::user()->can('buyers.delete'))
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash me-1"></i>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                @if($buyer->status !== 'deleted')
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
                                <form action="{{ route('buyer.destroy', $buyer->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">{{ __('buyer.delete') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

@endsection
