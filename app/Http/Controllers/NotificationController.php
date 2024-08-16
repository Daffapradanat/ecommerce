<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(10);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return redirect($notification->data['url'] ?? route('lobby.index'))->with('success', 'Notification marked as read');
    }

    public function destroy($id)
    {
        Auth::user()->notifications()->findOrFail($id)->delete();
        return back()->with('success', 'Notification deleted successfully');
    }

    public function deleteSelected(Request $request)
    {
        $selectedNotifications = $request->input('selected_notifications', []);
        Auth::user()->notifications()->whereIn('id', $selectedNotifications)->delete();
        return back()->with('success', 'Selected notifications deleted successfully.');
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
