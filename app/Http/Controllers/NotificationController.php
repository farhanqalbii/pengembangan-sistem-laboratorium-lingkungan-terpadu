<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back();
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai telah dibaca.',
            ]);
        }

        return back();
    }
}
