<?php

namespace App\Http\Controllers\Kaprodi;

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

        return view('kaprodi.notifikasi.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(Notification $notification)
    {
        $this->authorizeOwner($notification);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    /** Buka notifikasi: tandai dibaca lalu arahkan ke halaman terkait. */
    public function open(Notification $notification)
    {
        $this->authorizeOwner($notification);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return redirect($this->targetUrl($notification->category));
    }

    /** Peta kategori notifikasi → halaman tujuan. */
    private function targetUrl(?string $category): string
    {
        return match ($category) {
            'pengajuan_magang'  => route('kaprodi.pengajuan-magang.index'),
            'pengajuan_seminar' => route('kaprodi.seminar.index'),
            'selesai_magang'    => route('kaprodi.mahasiswa.index'),
            default             => route('kaprodi.notifikasi'),
        };
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
