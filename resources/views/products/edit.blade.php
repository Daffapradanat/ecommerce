@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h1 class="h3 mb-0">{{ __('products.edit_product') }}: {{ $product->name }}</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @include('products.partials.form')

                        <div class="text-end mt-4">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">{{ __('products.cancel') }}</a>
                            <button type="submit" class="btn btn-warning">{{ __('products.update_product') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
