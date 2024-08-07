@extends('layouts')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg rounded-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="mb-0 font-weight-bold">Pay Order #{{ $order->id }}</h3>
                </div>
                <div class="card-body">

                    <!-- Custom Notification -->
                    <div id="custom-notification" class="alert d-none mb-4" role="alert"></div>

                    <div class="alert alert-info mb-4">
                        <h5 class="mb-3">Time Remaining</h5>
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="countdown-item">
                                <span id="countdown-minutes" class="countdown-value">00</span>
                                <span class="countdown-label">Minutes</span>
                            </div>
                            <div class="countdown-separator">:</div>
                            <div class="countdown-item">
                                <span id="countdown-seconds" class="countdown-value">00</span>
                                <span class="countdown-label">Seconds</span>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 8px;">
                            <div id="countdown-progress" class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h6 class="mb-3">Order Details:</h6>
                            <p class="mb-1"><strong>Order ID:</strong> {{ $order->order_id }}</p>
                            <p class="mb-1"><strong>Date:</strong> {{ $order->created_at->format('d M Y H:i') }}</p>
                            <p class="mb-1"><strong>Status:</strong> <span class="badge bg-warning">Awaiting Payment</span></p>
                        </div>
                        <div class="col-sm-6">
                            <h6 class="mb-3">Customer Details:</h6>
                            <p class="mb-1"><strong>Name:</strong> {{ $order->name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $order->email }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $order->phone }}</p>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-end">Rp {{ number_format($order->total_price, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($snapToken)
                    <div class="text-center mt-4">
                        <button id="pay-button" class="btn btn-primary btn-lg px-5 py-3 mb-3">
                            <i class="fas fa-credit-card me-2"></i>Proceed to Payment
                        </button>

                        @if($order->payment_status === 'awaiting_payment' || $order->payment_status === 'pending')
                            <form action="{{ route('orders.cancel-payment', $order->id) }}" method="POST" class="mt-3">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-danger btn-lg px-5 py-3">
                                    <i class="fas fa-times me-2"></i>Cancel Order
                                </button>
                            </form>
                        @endif
                    </div>
                    @else
                    <div class="alert alert-danger">
                        Payment token is not available. Please try again later or contact support.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    let countdownInterval;
    let expirationTime = {{ session('payment_expires_at_' . $order->id) }} * 1000;
    let startTime = new Date().getTime();
    let totalDuration = expirationTime - startTime;

    function updateCountdown() {
        let now = new Date().getTime();
        let distance = expirationTime - now;

        if (distance < 0) {
            clearInterval(countdownInterval);
            document.getElementById("countdown-minutes").innerHTML = "00";
            document.getElementById("countdown-seconds").innerHTML = "00";
            document.getElementById("countdown-progress").style.width = "0%";
            showNotification("Payment time has expired. Please try again.", 'warning');
        } else {
            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("countdown-minutes").innerHTML = minutes.toString().padStart(2, '0');
            document.getElementById("countdown-seconds").innerHTML = seconds.toString().padStart(2, '0');

            let progressPercentage = ((totalDuration - distance) / totalDuration) * 100;
            document.getElementById("countdown-progress").style.width = (100 - progressPercentage) + "%";
        }
    }

    countdownInterval = setInterval(updateCountdown, 1000);
    updateCountdown();

    function showNotification(message, type = 'info') {
        const notification = document.getElementById('custom-notification');
        notification.className = `alert alert-${type} d-block mb-4`;
        notification.textContent = message;
        setTimeout(() => {
            notification.className = 'alert d-none mb-4';
        }, 5000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        let payButton = document.getElementById('pay-button');
        if(payButton) {
            payButton.onclick = function () {
                snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result){
                        console.log('Payment success:', result);
                        showNotification("Payment completed successfully!", 'success');

                        setTimeout(() => {
                            window.location.href = "{{ route('orders.show', $order->id) }}";
                        }, 3000);
                    },
                    onPending: function(result){
                        console.log('Payment pending:', result);
                        showNotification("Payment is pending. Please complete your payment.", 'info');
                    },
                    onError: function(result){
                        console.log('Payment error:', result);
                        showNotification("Payment failed. Please try again.", 'danger');
                    },
                    onClose: function(){
                        console.log('Customer closed the popup without finishing the payment');
                        showNotification("Payment cancelled. You can try again later.", 'warning');
                    }
                });
            };
        } else {
            console.error('Payment button not found');
        }
    });

    function checkPaymentStatus() {
        fetch("{{ route('orders.check-payment', $order->id) }}")
            .then(response => response.json())
            .then(data => {
                if (data.status === 'paid') {
                    alert("Payment completed successfully!");
                    window.location.href = data.redirect;
                } else if (data.status === 'failed') {
                    alert("Payment failed. Order has been cancelled.");
                    window.location.href = "{{ route('orders.index') }}";
                } else if (data.status === 'awaiting_payment') {
                    setTimeout(checkPaymentStatus, 5000); // Check again after 5 seconds
                } else {
                    // If status is 'pending' or any other status
                    alert("Payment process cancelled or timed out.");
                    window.location.href = "{{ route('orders.show', $order->id) }}";
                }
            });
    }

    function updateStatusDisplay(newStatus) {
        const statusElement = document.querySelector('.badge');
        if (statusElement) {
            statusElement.textContent = `Payment Status: ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}`;
            statusElement.className = `badge bg-${newStatus === 'paid' ? 'success' : 'secondary'} fs-6`;
        }
    }
</script>
@endsection

@push('styles')
<style>
    .card {
        border: none;
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    .btn-lg {
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    #countdown {
        font-size: 1.2rem;
    }
    .progress {
        height: 8px;
        border-radius: 4px;
    }
    .countdown-item {
        text-align: center;
        margin: 0 10px;
    }

    .countdown-value {
        font-size: 2.5rem;
        font-weight: bold;
        display: block;
        line-height: 1;
    }

    .countdown-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .countdown-separator {
        font-size: 2.5rem;
        font-weight: bold;
        line-height: 1;
        align-self: flex-start;
        padding-top: 5px;
    }

    .progress {
        height: 8px;
        border-radius: 4px;
    }

    .progress-bar {
        transition: width 1s linear;
    }
    .table th, .table td {
        vertical-align: middle;
    }
</style>
@endpush
