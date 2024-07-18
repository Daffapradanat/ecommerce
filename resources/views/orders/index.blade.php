@extends('layouts')

@section('content')
<div class="container">
    <h1 class="mb-4">All Orders</h1>
    @forelse($orders as $order)
        <div class="card mb-4 shadow-sm" data-order-id="{{ $order->id }}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap">
                <span class="fw-bold">Order #{{ $order->id }}</span>
                <span class="text-muted">{{ $order->created_at->format('d M Y H:i') }}</span>
                <span class="badge bg-{{ $order->payment_status === 'pending' ? 'warning' : ($order->payment_status === 'paid' ? 'success' : 'danger') }} mt-2 mt-md-0 payment-status-badge">
                    {{ ucfirst($order->payment_status) }}
                </span>
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
            <div class="card-footer">
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary btn-sm">Show Details</a>
                <button class="btn btn-danger btn-sm delete-order" data-order-id="{{ $order->id }}">Delete Order</button>
            </div>
        </div>
    @empty
        <div class="alert alert-info">No orders found.</div>
    @endforelse
</div>

<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this order?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let deleteOrderButtons = document.querySelectorAll('.delete-order');
        let deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        let confirmDeleteButton = document.getElementById('confirmDelete');
        let orderIdToDelete;

        deleteOrderButtons.forEach(button => {
            button.addEventListener('click', function() {
                orderIdToDelete = this.getAttribute('data-order-id');
                deleteConfirmationModal.show();
            });
        });

        confirmDeleteButton.addEventListener('click', function() {
            if (orderIdToDelete) {
                // Send delete request
                fetch(`/api/orders/${orderIdToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the order card from the DOM
                        let orderCard = document.querySelector(`[data-order-id="${orderIdToDelete}"]`);
                        if (orderCard) {
                            orderCard.remove();
                        }
                        // Show success message
                        alert('Order deleted successfully');
                    } else {
                        // Show error message
                        alert('Failed to delete order');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the order');
                })
                .finally(() => {
                    deleteConfirmationModal.hide();
                });
            }
        });
    });
</script>
@endpush

@endsection
