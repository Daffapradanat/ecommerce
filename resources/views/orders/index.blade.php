@extends('layouts')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Order Management</h1>

    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="ordersTable">
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
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Order Cancellation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to cancel this order?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmCancelButton">Cancel Order</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')

@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    var table = $('#ordersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('orders.index') }}",
        columns: [
            {data: 'order_id', name: 'order_id'},
            {data: 'buyer.name', name: 'buyer.name'},
            {data: 'total_price', name: 'total_price'},
            {data: 'payment_status', name: 'payment_status', searchable: false},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    $('#ordersTable').on('click', '.delete-order', function() {
        var orderId = $(this).data('id');
        $('#deleteModal').modal('show');
        $('#confirmCancelButton').data('id', orderId);
    });

    $('#confirmCancelButton').on('click', function() {
        var orderId = $(this).data('id');
        $.ajax({
            url: "{{ url('orders') }}/" + orderId + "/cancel",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                table.ajax.reload();
                Swal.fire('Cancelled!', 'The order has been cancelled.', 'success');
            },
            error: function(xhr) {
                $('#deleteModal').modal('hide');
                Swal.fire('Error!', 'There was an error cancelling the order.', 'error');
            }
        });
    });

    // Alert auto-close
    window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function(){
            $(this).remove();
        });
    }, 5000);
});
</script>
@endpush
