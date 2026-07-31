<?php

namespace App\Http\Controllers\DosenIndustri;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->orderByDesc('created_at')
            ->paginate(15);

        $unreadCount = $user->notifications()->where('is_read', false)->count();

        return view('dosen-industri.notifikasi.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(Notification $notification)
    {
        $this->authorizeOwner($notification);

        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    /** Buka notifikasi: tandai dibaca lalu arahkan ke halaman yang dirujuk. */
    public function open(Notification $notification)
    {
        $this->authorizeOwner($notification);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return redirect($notification->link ?: route('dosen-industri.notifikasi'));
    }

    public function markAllRead()
    {
        Auth::user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    private function authorizeOwner(Notification $notification): void
    {
        if ($notification->user_id !== Auth::id()) abort(403);
    }
}
