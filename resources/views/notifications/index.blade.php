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
                <ul class="list-group notifications-list">
                    @foreach($notifications as $notification)
                        <li class="list-group-item d-flex justify-content-between align-items-center {{ $notification->read_at ? 'bg-light' : '' }}">
                            <div class="form-check">
                                <input class="form-check-input notification-checkbox" type="checkbox" name="selected_notifications[]" value="{{ $notification->id }}" id="notification{{ $notification->id }}">
                                <label class="form-check-label {{ $notification->read_at ? 'text-muted' : 'fw-bold' }}" for="notification{{ $notification->id }}">
                                    {{ $notification->data['message'] }}
                                </label>
                            </div>
                            <div>
                                @if(!$notification->read_at)
                                    <a href="{{ route('notifications.markAsRead', $notification->id) }}" class="btn btn-sm btn-primary me-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                                <button type="button" class="btn btn-sm btn-danger delete-notification" data-notification-id="{{ $notification->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </form>
            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteNotificationModal" tabindex="-1" aria-labelledby="deleteNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteNotificationModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this notification?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteNotificationForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#refreshButton').click(function() {
            location.reload();
        });

        $('#markAllReadButton').click(function() {
            $('#notificationForm').attr('action', '{{ route("notifications.markAllAsRead") }}').submit();
        });

        $('.delete-notification').click(function() {
            var notificationId = $(this).data('notification-id');
            $('#deleteNotificationForm').attr('action', 'notifications/' + notificationId);
            $('#deleteNotificationModal').modal('show');
        });

        $('#selectAll').change(function() {
            $('.notification-checkbox').prop('checked', $(this).prop('checked'));
        });

        $('#deleteSelectedButton').click(function() {
            if(confirm('Are you sure you want to delete selected notifications?')) {
                $('#notificationForm').attr('action', '{{ route("notifications.deleteSelected") }}').submit();
            }
        });
    });
</script>
@endpush
