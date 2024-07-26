@extends('layouts')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Order Management</h1>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('orders.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by Order ID or Customer" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Tabs -->
    <div class="card mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="orderTabs" role="tablist">
                @foreach(['All', 'Pending', 'Awaiting Payment', 'Paid', 'Failed & Cancelled'] as $status)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                id="{{ Str::slug($status) }}-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#{{ Str::slug($status) }}"
                                type="button"
                                role="tab"
                                aria-controls="{{ Str::slug($status) }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            {{ $status }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="orderTabsContent">
                @foreach(['All', 'Pending', 'Awaiting Payment', 'Paid', 'Failed & Cancelled'] as $status)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                         id="{{ Str::slug($status) }}"
                         role="tabpanel"
                         aria-labelledby="{{ Str::slug($status) }}-tab">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders->filter(function($order) use ($status) {
                                        if ($status === 'All') {
                                            return true;
                                        } elseif ($status === 'Failed & Cancelled') {
                                            return in_array(strtolower($order->payment_status), ['failed', 'cancelled']);
                                        } else {
                                            return strtolower($order->payment_status) === strtolower(str_replace(' ', '_', $status));
                                        }
                                    }) as $order)
                                        <tr>
                                            <td>{{ $order->order_id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($order->buyer->image)
                                                        <img src="{{ asset('storage/buyers/'.$order->buyer->image) }}" alt="{{ $order->buyer->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2" style="width: 40px; height: 40px;">
                                                            {{ strtoupper(substr($order->buyer->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $order->buyer->name }}</div>
                                                        <div class="text-muted small">{{ $order->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $order->payment_status === 'pending' ? 'warning' :
                                                                         ($order->payment_status === 'awaiting_payment' ? 'info' :
                                                                         ($order->payment_status === 'paid' ? 'success' : 'danger')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($order->payment_status === 'pending')
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $order->id }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No orders found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
</div>

@foreach($orders as $order)
    <!-- Delete Modal for each order -->
    <div class="modal fade" id="deleteModal{{ $order->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $order->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel{{ $order->id }}">Confirm Order Cancellation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to cancel Order #{{ $order->order_id }}?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" onclick="confirmCancel({{ $order->id }})">Cancel Order</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection

@push('scripts')
<script>
    function refreshOrders() {
        $.ajax({
            url: '{{ route('orders.index') }}',
            method: 'GET',
            success: function(response) {
                $('#orderTabsContent').html($(response).find('#orderTabsContent').html());
            }
        });
    }

    // Refresh the orders every 30 seconds
    setInterval(refreshOrders, 30000);

    function confirmCancel(orderId) {
        if (confirm('Are you sure you want to cancel this order?')) {
            window.location.href = "{{ url('orders') }}/" + orderId + "/cancel";
        }
    }
</script>
@endpush
