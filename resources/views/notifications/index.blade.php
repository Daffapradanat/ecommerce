@extends('layouts')

@section('content')
{{-- <div class="mt-4">
    <h2>Create New Notification</h2>
    <form id="createNotificationForm">
        @csrf
        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <input type="text" class="form-control" id="message" name="message" required>
        </div>
        <div class="mb-3">
            <label for="url" class="form-label">URL (optional)</label>
            <input type="text" class="form-control" id="url" name="url">
        </div>
        <button type="submit" class="btn btn-primary">Create Notification</button>
    </form>
</div> --}}
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h1 class="mb-0 me-3">Notifications</h1>
                    <div class="d-flex flex-wrap gap-2">
                        <button id="refreshButton" class="btn btn-outline-secondary">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button id="markAllReadButton" class="btn btn-outline-primary">
                            <i class="fas fa-check-double"></i> Mark All as Read
                        </button>
                        <button id="deleteSelectedButton" class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>
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

    <!-- Selected Delete Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkDeleteModalLabel">Confirm Selected Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete these notifications?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmBulkDeleteButton">Delete Notifications</button>
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
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let table = $('#notificationsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('notifications.getNotifications') }}",
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="notification-checkbox" value="' +
                                row.id + '">';
                        }
                    },
                    {
                        data: 'message',
                        name: 'message'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#refreshButton').click(function() {
                table.ajax.reload();
            });

            $('#markAllReadButton').click(function() {
                $.ajax({
                    url: '{{ route('notifications.markAllAsRead') }}',
                    type: 'POST',
                    success: function(result) {
                        table.ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to mark all as read: ' + xhr.responseText);
                    }
                });
            });

            let notificationIdToDelete;
            $(document).on('click', '.delete-notification', function() {
                notificationIdToDelete = $(this).data('notification-id');
                $('#deleteModal').modal('show');
            });

            $('#confirmDeleteButton').click(function() {
                $.ajax({
                    url: '{{ route('notifications.destroy', '') }}/' + notificationIdToDelete,
                    type: 'DELETE',
                    success: function(result) {
                        $('#deleteModal').modal('hide');
                        table.ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to delete notification: ' + xhr.responseText);
                    }
                });
            });

            $('#selectAll').change(function() {
                $('.notification-checkbox').prop('checked', $(this).prop('checked'));
            });

            let selectedIds = [];
            $('#deleteSelectedButton').click(function() {
                selectedIds = $('.notification-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {
                    $('#bulkDeleteModal').modal('show');
                } else {
                    alert('Please select at least one notification to delete.');
                }
            });

            $('#confirmBulkDeleteButton').click(function() {
                $.ajax({
                    url: '{{ route('notifications.deleteSelected') }}',
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "selected_notifications": selectedIds
                    },
                    success: function(result) {
                        $('#bulkDeleteModal').modal('hide');
                        table.ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to delete selected notifications: ' + xhr.responseText);
                    }
                });
            });
        });
    </script>
@endpush
