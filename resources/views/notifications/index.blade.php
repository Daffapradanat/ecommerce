@extends('layouts')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Notifications</h1>
                <div>
                    <button id="refreshButton" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button id="markAllReadButton" class="btn btn-outline-primary me-2">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </button>
                    <button id="deleteSelectedButton" class="btn btn-outline-danger">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="notificationForm" action="{{ route('notifications.batchAction') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">
                                Select All
                            </label>
                        </div>
                    </div>
                    <table id="notificationsTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Message</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Notification Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this notification?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Delete Notification</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            let table = $('#notificationsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('notifications.getNotifications') }}",
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    { data: 'message', name: 'message' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });

            $('#refreshButton').click(function() {
                table.ajax.reload();
            });

            $('#markAllReadButton').click(function() {
                $('#notificationForm').attr('action', '{{ route('notifications.markAllAsRead') }}').submit();
            });

            let notificationIdToDelete;

            $(document).on('click', '.delete-notification', function() {
                notificationIdToDelete = $(this).data('notification-id');
                $('#deleteModal').modal('show');
            });

            $('#confirmDeleteButton').click(function() {
                $.ajax({
                    url: 'notifications/' + notificationIdToDelete,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(result) {
                        $('#deleteModal').modal('hide');
                        table.ajax.reload();
                    }
                });
            });

            $('#selectAll').change(function() {
                $('.notification-checkbox').prop('checked', $(this).prop('checked'));
            });

            $('#deleteSelectedButton').click(function() {
                if ($('.notification-checkbox:checked').length > 0) {
                    $('#deleteModal').modal('show');
                } else {
                    alert('Please select at least one notification to delete.');
                }
            });
        });
    </script>
@endpush
