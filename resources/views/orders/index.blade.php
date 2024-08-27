@extends('layouts')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <h1 class="mt-4 mb-3 mb-md-0">{{ __('order.order_management') }}</h1>
        <div>
            <a href="{{ route('orders.export') }}" class="btn btn-info">
                <i class="fas fa-file-export"></i> {{ __('order.export_orders') }}
            </a>
        </div>
    </div>
</div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="ordersTable">
                    <thead>
                        <tr>
                            <th>{{ __('order.order_id') }}</th>
                            <th>{{ __('order.customer') }}</th>
                            <th>{{ __('order.total') }}</th>
                            <th>{{ __('order.status') }}</th>
                            <th>{{ __('order.date') }}</th>
                            <th>{{ __('order.actions') }}</th>
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
                <h5 class="modal-title" id="deleteModalLabel">{{ __('order.confirm_order_cancellation') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ __('order.cancel_order_confirmation') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('order.close') }}</button>
                <button type="button" class="btn btn-danger" id="confirmCancelButton">{{ __('order.cancel_order') }}</button>
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
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                cancelOrder(orderId);
            }
        });
    });

    function cancelOrder(orderId) {
        $.ajax({
            url: "{{ route('orders.cancel', ':id') }}".replace(':id', orderId),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                table.ajax.reload();
                Swal.fire('Cancelled!', response.message, 'success');
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON.message || 'There was an error cancelling the order.', 'error');
            }
        });
    }

    window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function(){
            $(this).remove();
        });
    }, 5000);
});
</script>
@endpush
