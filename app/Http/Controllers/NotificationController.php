<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $notifications = $request->user()
            ->notifications()
            ->when($filter === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($filter === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, DatabaseNotification $notification)
    {
        abort_unless((int) $notification->notifiable_id === (int) $request->user()->id, 403);

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('notifications.index'));
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function deleteRead(Request $request)
    {
        $request->user()
            ->readNotifications()
            ->delete();

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Read notifications deleted successfully.');
    }

    public function deleteAll(Request $request)
    {
        $request->user()
            ->notifications()
            ->delete();

        return redirect()
            ->route('notifications.index')
            ->with('success', 'All notifications deleted successfully.');
    }

    public function destroy(Request $request, DatabaseNotification $notification)
    {
        abort_unless((int) $notification->notifiable_id === (int) $request->user()->id, 403);

        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }
}
