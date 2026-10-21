<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($notification, Request $request)
    {
        $notif = $notification instanceof Notification
            ? $notification
            : Notification::findOrFail($notification);

        if ((int) $notif->user_id !== (int) auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        $notif->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi telah ditandai dibaca.',
                'link' => $notif->link,
                'counts' => Notification::getUnreadCountsFor(auth()->id()),
            ]);
        }

        if ($notif->link) {
            return redirect($notif->link);
        }

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai telah dibaca.',
                'counts' => [
                    'total' => 0,
                    'sample' => 0,
                    'borrowing' => 0,
                    'coa' => 0,
                    'payment' => 0,
                    'equipment' => 0,
                    'verification' => 0,
                    'finance' => 0,
                ],
            ]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function getCounts()
    {
        return response()->json([
            'success' => true,
            'counts' => Notification::getUnreadCountsFor(auth()->id()),
        ]);
    }
}
