@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0">{{ __('products.create_new_product') }}</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @include('products.partials.form')

                        <div class="text-end mt-4">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">{{ __('products.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('products.create_product') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if(session('notification'))
    <div class="alert alert-{{ session('notification')['type'] }} alert-dismissible fade show" role="alert">
        {{ session('notification')['message'] }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
