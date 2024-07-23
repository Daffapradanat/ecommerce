@extends('layouts')

@section('content')
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
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Time remaining:</span>
                            <span id="countdown" class="font-weight-bold"></span>
                        </div>
                        <div class="progress mt-2" style="height: 5px;">
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
                                    <td>{{ $item->product->name }}</td>
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
                        <button id="cancel-payment" class="btn btn-outline-secondary btn-lg px-5 py-3 mb-3">
                            <i class="fas fa-times me-2"></i>Cancel Payment
                        </button>
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

    function updateCountdown() {
        let now = new Date().getTime();
        let distance = expirationTime - now;

        if (distance < 0) {
            clearInterval(countdownInterval);
            document.getElementById("countdown").innerHTML = "EXPIRED";
            updatePaymentStatus('failed');
        } else {
            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);
            document.getElementById("countdown").innerHTML = minutes + "m " + seconds + "s ";
        }
    }

    countdownInterval = setInterval(updateCountdown, 1000);


    // Custom notification function
    function showNotification(message, type = 'info') {
        const notification = document.getElementById('custom-notification');
        notification.className = `alert alert-${type} d-block mb-4`;
        notification.textContent = message;
        setTimeout(() => {
            notification.className = 'alert d-none mb-4';
        }, 5000);
    }

    // Payment button logic
    document.getElementById('pay-button').onclick = function () {
        snap.pay('{{ $snapToken }}', {
            onSuccess: function(result){
                updatePaymentStatus('paid');
            },
            onPending: function(result){
                updatePaymentStatus('awaiting_payment');
            },
            onError: function(result){
                updatePaymentStatus('failed');
            },
            onClose: function(){
                if (confirm('Are you sure you want to cancel the payment process?')) {
                    updatePaymentStatus('pending');
                }
            }
        });
    };

    function completePayment() {
        fetch("{{ route('orders.complete-payment', $order->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = "{{ route('orders.show', $order->id) }}";
            } else {
                alert("Error updating payment status. Please contact support.");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred. Please try again or contact support.");
        });
    }

    // Cancel payment button logic
    document.getElementById('cancel-payment').onclick = function () {
        if (confirm('Are you sure you want to cancel the payment process?')) {
            cancelPayment();
        }
    };

    function cancelPayment() {
        fetch("{{ route('orders.cancel-payment', $order->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    window.location.href = "{{ route('orders.show', $order->id) }}";
                }, 2000);
            } else {
                showNotification(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification("An error occurred. Please try again or contact support.", 'danger');
        });
    }

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

    function updatePaymentStatus(status) {
        fetch("{{ route('orders.update-payment-status', $order->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.new_status === 'paid') {
                    showNotification("Payment completed successfully!", 'success');
                    setTimeout(() => {
                        window.location.href = "{{ route('orders.show', $order->id) }}";
                    }, 2000);
                } else if (data.new_status === 'awaiting_payment') {
                    showNotification("Awaiting payment. Please complete your payment.", 'info');
                } else if (data.new_status === 'failed') {
                    showNotification("Payment failed. Please try again.", 'danger');
                    setTimeout(() => {
                        window.location.href = "{{ route('orders.show', $order->id) }}";
                    }, 2000);
                }
            } else {
                showNotification("Error updating payment status. Please contact support.", 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification("An error occurred. Please try again or contact support.", 'danger');
        });
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
    .table th, .table td {
        vertical-align: middle;
    }
</style>
@endpush
