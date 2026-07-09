<?php

namespace App\Http\Controllers\Api\Dosen;

use App\Http\Controllers\Api\ApiController;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function index(Request $request)
    {
        $this->currentLecturer($request, 'lecturer');
        $user = $request->user();

        return response()->json([
            'unread_count'  => $user->notifications()->where('is_read', false)->count(),
            'notifications' => $user->notifications()->orderByDesc('created_at')->paginate(15)
                ->through(fn ($n) => [
                    'id'          => $n->id,
                    'message'     => $n->message,
                    'category'    => $n->category,
                    'date'        => $n->date,
                    'is_read'     => (bool) $n->is_read,
                    'detail_text' => $n->detail_text,
                ]),
        ]);
    }

    public function markRead(Request $request, Notification $notification)
    {
        $this->currentLecturer($request, 'lecturer');
        abort_if($notification->user_id !== $request->user()->id, 403, 'Akses ditolak.');
        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }
        return response()->json(['message' => 'Notifikasi ditandai sudah dibaca.']);
    }

    public function markAllRead(Request $request)
    {
        $this->currentLecturer($request, 'lecturer');
        $request->user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca.']);
    }
}
