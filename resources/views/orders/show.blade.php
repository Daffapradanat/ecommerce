@extends('layouts')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $order->order_id }}</h4>
                    <div>
                        @php
                            $paymentStatusClasses = [
                                'pending' => 'warning',
                                'awaiting_payment' => 'info',
                                'paid' => 'success',
                                'failed' => 'danger'
                            ];
                            $paymentStatusClass = $paymentStatusClasses[$order->payment_status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $paymentStatusClass }} fs-6">
                            Payment Status: {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                        </span>
                    </div>
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
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-end">Rp {{ number_format($item->product_price, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($item->product_price * $item->quantity, 0, ',', '.') }}</td>
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
                <div class="card-footer bg-white p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <p class="mb-0 fw-bold"><i class="fas fa-credit-card me-2"></i>Payment Method:
                                <span class="fw-normal">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Not available')) }}</span>
                            </p>
                        </div>
                        <div class="col-lg-6 text-lg-end">
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-2"></i>Back to Orders
                            </a>
                            @if($order->payment_status === 'pending' || $order->payment_status === 'awaiting_payment')
                                <a href="{{ route('orders.pay', $order->id) }}" class="btn btn-primary">
                                    <i class="fas fa-credit-card me-2"></i>Pay Now
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(isset($snapToken))
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Fungsi untuk memperbarui tampilan status
    function updateStatusDisplay(newStatus) {
        const statusBadge = document.querySelector('.badge');
        const statusClasses = {
            'pending': 'bg-warning',
            'awaiting_payment': 'bg-info',
            'paid': 'bg-success',
            'failed': 'bg-danger'
        };
        statusBadge.className = `badge ${statusClasses[newStatus] || 'bg-secondary'} fs-6`;
        statusBadge.textContent = `Payment Status: ${newStatus.replace('_', ' ').charAt(0).toUpperCase() + newStatus.slice(1)}`;
    }

    // Cek status pembayaran saat halaman dimuat
    fetch("{{ route('orders.check-payment', $order->id) }}")
        .then(response => response.json())
        .then(data => {
            if (data.status !== '{{ $order->payment_status }}') {
                updateStatusDisplay(data.status);
            }
        });
});
</script>
@endpush
@endif

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(45deg, #4e73df, #36b9cc);
    }
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    .table th, .table td {
        padding: 1rem;
    }
    @media (max-width: 768px) {
        .table th, .table td {
            padding: 0.75rem;
        }
    }
</style>
@endpush
