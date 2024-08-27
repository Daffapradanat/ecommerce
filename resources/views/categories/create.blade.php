@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0">{{ __('category_list.create_category') }}</h1>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('category_list.category_name') }}</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary me-2">{{ __('category_list.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('category_list.create_category') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
