@extends('layouts')

@section('content')
<div class="container">
    <h1 class="mb-4">All Orders</h1>
    @foreach($orders as $order)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <span>Order #{{ $order->id }} - {{ $order->created_at->format('d M Y H:i') }}</span>
                <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : 'success' }} mt-2 mt-md-0">{{ ucfirst($order->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <h5 class="card-title">Total: ${{ number_format($order->total_price, 2) }}</h5>
                        <div class="d-flex align-items-center">
                            @if($order->user->avatar)
                                <img src="{{ asset('storage/'.$order->user->avatar) }}" alt="{{ $order->user->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2" style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($order->user->name, 0, 1)) }}
                                </div>
                            @endif
                            <p class="card-text mb-0">{{ $order->user->name }} ({{ $order->user->email }})</p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $item)
                                <tr>
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
