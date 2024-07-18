@extends('layouts')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Order #{{ $order->id }}</h4>
                    <span class="badge bg-{{ $order->payment_status === 'pending' ? 'warning' : ($order->payment_status === 'paid' ? 'success' : 'danger') }} fs-6">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h6 class="mb-3">From:</h6>
                            <div><strong>Your Store Name</strong></div>
                            <div>123 Store Street</div>
                            <div>Store City, ST 12345</div>
                            <div>Email: store@example.com</div>
                            <div>Phone: +1 234 567 8901</div>
                        </div>
                        <div class="col-sm-6">
                            <h6 class="mb-3">To:</h6>
                            <div><strong>{{ $order->name }}</strong></div>
                            <div>{{ $order->address }}</div>
                            <div>{{ $order->city }}, {{ $order->postal_code }}</div>
                            <div>Email: {{ $order->email }}</div>
                            <div>Phone: {{ $order->phone }}</div>
                        </div>
                    </div>

                    <div class="table-responsive-sm">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-sm-5 ms-auto">
                            <table class="table table-clear">
                                <tbody>
                                    <tr>
                                        <td class="left"><strong>Total</strong></td>
                                        <td class="text-end"><strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <p class="mb-0"><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Not available')) }}</p>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            @if($order->payment_status == 'pending')
                                <button class="btn btn-primary" onclick="payNow('{{ $order->id }}')">Pay Now</button>
                            @endif
                            <a href="{{ route('orders.index') }}" class="btn btn-secondary">Back to Orders</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
  function payNow(orderId) {
        fetch(`/api/orders/${orderId}/payment-link`)
            .then(response => response.json())
            .then(data => {
                if (data.payment_url) {
                    window.location.href = data.payment_url;
                } else {
                    alert('Failed to get payment link. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while getting the payment link.');
            });
    }
</script>
@endpush

@endsection
