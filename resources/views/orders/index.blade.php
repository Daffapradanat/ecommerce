@extends('layouts')

@section('content')
<div class="container">
    <h1 class="mb-4">All Orders</h1>
    @forelse($orders as $order)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap">
                <span class="fw-bold">Order #{{ $order->id }}</span>
                <span class="text-muted">{{ $order->created_at->format('d M Y H:i') }}</span>
                <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'completed' ? 'success' : 'danger') }} mt-2 mt-md-0">{{ ucfirst($order->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5 class="card-title text-primary">Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}</h5>
                        <div class="d-flex align-items-center mb-3">
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
                                <p class="card-text mb-0 fw-bold">{{ $order->name }}</p>
                                <p class="card-text text-muted mb-0">{{ $order->email }}</p>
                                <p class="card-text text-muted mb-0">{{ $order->phone }}</p>
                            </div>
                        </div>
                        <div>
                            <h6 class="fw-bold">Shipping Address:</h6>
                            <p class="mb-0">{{ $order->address }}</p>
                            <p class="mb-0">{{ $order->city }}, {{ $order->postal_code }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <p class="mb-1"><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
                        <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Not available')) }}</p>
                        @if($order->payment_token && $order->payment_status == 'pending')
                            <button class="btn btn-primary btn-sm mt-2" onclick="payNow('{{ $order->payment_token }}')">Pay Now</button>
                        @endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">No orders found.</div>
    @endforelse
</div>

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    function payNow(token) {
        snap.pay(token, {
            onSuccess: function(result){
                alert("Payment success!");
                location.reload();
            },
            onPending: function(result){
                alert("Waiting for your payment!");
            },
            onError: function(result){
                alert("Payment failed!");
            },
            onClose: function(){
                alert('You closed the popup without finishing the payment');
            }
        });
    }
</script>
@endpush

@endsection
