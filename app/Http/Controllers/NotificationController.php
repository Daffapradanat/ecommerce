<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    public function index()
    {
        return view('notifications.index');
    }

    public function getNotifications()
    {
        $notifications = Auth::user()->notifications();

        return DataTables::of($notifications)
            ->addColumn('checkbox', function ($notification) {
                return '<input type="checkbox" name="selected_notifications[]" value="' . $notification->id . '" class="notification-checkbox">';
            })
            ->addColumn('message', function ($notification) {
                $class = $notification->read_at ? 'text-muted' : 'fw-bold';
                return '<span class="' . $class . '">' . $notification->data['message'] . '</span>';
            })
            ->addColumn('actions', function ($notification) {
                $actions = '';
                if (!$notification->read_at) {
                    $actions .= '<a href="' . route('notifications.markAsRead', $notification->id) . '" class="btn btn-sm btn-primary me-2"><i class="fas fa-eye"></i></a>';
                }
                $actions .= '<button type="button" class="btn btn-sm btn-danger delete-notification" data-notification-id="' . $notification->id . '"><i class="fas fa-trash"></i></button>';
                return $actions;
            })
            ->rawColumns(['checkbox', 'message', 'actions'])
            ->make(true);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return redirect($notification->data['url'] ?? route('lobby.index'))->with('success', 'Notification marked as read');
    }

    public function destroy($id)
    {
        try {
            $notification = Auth::user()->notifications()->findOrFail($id);
            $notification->delete();
            return response()->json(['success' => 'Notification deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete the notification'], 500);
        }
    }

    public function deleteSelected(Request $request)
    {
        $validated = $request->validate([
            'selected_notifications' => 'required|array|min:1',
            'selected_notifications.*' => 'exists:notifications,id',
        ]);

        try {
            Auth::user()->notifications()->whereIn('id', $validated['selected_notifications'])->delete();
            return response()->json(['success' => 'Selected notifications deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete selected notifications.'], 500);
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read');
    }

    public function batchAction(Request $request)
    {
        $action = $request->input('action');
        $selectedNotifications = $request->input('selected_notifications', []);

        if ($action === 'delete') {
            Auth::user()->notifications()->whereIn('id', $selectedNotifications)->delete();
            return back()->with('success', 'Selected notifications deleted');
        } elseif ($action === 'mark_read') {
            Auth::user()->notifications()->whereIn('id', $selectedNotifications)->update(['read_at' => now()]);
            return back()->with('success', 'Selected notifications marked as read');
        }

        return back()->with('error', 'Invalid action');
    }
}
