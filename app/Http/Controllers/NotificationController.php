<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        $unreadCount = auth()->user()->unreadNotifications->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', __('messages.marked_all_read'));
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications->count(),
        ]);
    }
}
