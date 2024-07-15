@extends('layouts')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h1 class="h3 mb-0">Buyer Details</h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="text-center">
                                @if($buyer->image)
                                    <img src="{{ asset('storage/buyers/' . $buyer->image) }}" alt="{{ $buyer->name }}" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                                        <span class="text-white" style="font-size: 48px;">{{ strtoupper(substr($buyer->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h2 class="h4 mb-3">{{ $buyer->name }}</h2>
                            <dl class="row">
                                <dt class="col-sm-3">Email</dt>
                                <dd class="col-sm-9">{{ $buyer->email }}</dd>

                                <dt class="col-sm-3">Created at</dt>
                                <dd class="col-sm-9">{{ $buyer->created_at->format('d M Y H:i') }}</dd>

                                <dt class="col-sm-3">Updated at</dt>
                                <dd class="col-sm-9">{{ $buyer->updated_at->format('d M Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('buyer.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('buyer.edit', $buyer->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection