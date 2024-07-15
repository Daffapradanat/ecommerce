@extends('layouts')

@section('content')
<div class="container">
    <h1 class="mb-4">All Orders</h1>
    @foreach($orders as $order)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap">
                <span class="fw-bold">Order #{{ $order->id }}</span>
                <span class="text-muted">{{ $order->created_at->format('d M Y H:i') }}</span>
                <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : 'success' }} mt-2 mt-md-0">{{ ucfirst($order->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <h5 class="card-title text-primary">Total: ${{ number_format($order->total_price, 2) }}</h5>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                @if($order->buyer->image)
                                    <img src="{{ asset('storage/buyers/'.$order->buyer->image) }}" alt="{{ $order->buyer->name }}" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                                        {{ strtoupper(substr($order->buyer->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="card-text mb-0 fw-bold">{{ $order->buyer->name }}</p>
                                <p class="card-text text-muted mb-0">{{ $order->buyer->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                {{-- <th>Image</th> --}}
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $item)
                                <tr>
                                    {{-- <td>
                                        @if($item->product->image && count($item->product->image) > 0)
                                            <img src="{{ asset('storage/' . $item->product->image[0]) }}" alt="{{ $item->product->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                No Image
                                            </div>
                                        @endif
                                    </td> --}}
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
