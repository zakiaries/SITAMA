<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Seminar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeminarController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $counts = [
            'pending'   => Seminar::whereNotNull('student_id')->where('status', 'pending')->count(),
            'scheduled' => Seminar::whereNotNull('student_id')->where('status', 'scheduled')->count(),
            'rejected'  => Seminar::whereNotNull('student_id')->where('status', 'rejected')->count(),
        ];

        $seminars = Seminar::with(['student.user', 'attendances'])
            ->whereNotNull('student_id')
            ->where('status', $status)
            ->orderByDesc('date')
            ->get();

        return view('kaprodi.seminar.index', compact('seminars', 'status', 'counts'));
    }

    /** ACC jadwal: seminar disetujui, QR/berita acara diaktifkan. */
    public function approve(Seminar $seminar)
    {
        if ($seminar->status !== 'pending') {
            return back()->with('error', 'Pengajuan seminar ini sudah diproses.');
        }

        $seminar->update([
            'status'           => 'scheduled',
            'rejection_reason' => null,
            'access_token'     => $seminar->access_token ?: Str::random(48),
        ]);

        $this->notifyStudent(
            $seminar,
            "Jadwal seminar \"{$seminar->title}\" disetujui Kaprodi.",
            'Silakan buka detail seminar untuk mendapatkan QR daftar hadir (berita acara).'
        );

        return back()->with('success', "Jadwal seminar {$seminar->student->user->name} disetujui.");
    }

    /** Tolak jadwal: mahasiswa dapat mengajukan tanggal lain. */
    public function reject(Request $request, Seminar $seminar)
    {
        if ($seminar->status !== 'pending') {
            return back()->with('error', 'Pengajuan seminar ini sudah diproses.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $seminar->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        $this->notifyStudent(
            $seminar,
            "Jadwal seminar \"{$seminar->title}\" ditolak Kaprodi.",
            'Alasan: ' . $request->rejection_reason . ' — silakan ajukan jadwal baru.'
        );

        return back()->with('success', "Jadwal seminar {$seminar->student->user->name} ditolak.");
    }

    private function notifyStudent(Seminar $seminar, string $message, string $detail): void
    {
        $userId = $seminar->student?->user?->id;
        if (!$userId) {
            return;
        }

        Notification::create([
            'user_id'     => $userId,
            'message'     => $message,
            'date'        => now()->toDateString(),
            'category'    => 'seminar',
            'is_read'     => false,
            'detail_text' => $detail,
        ]);
    }
}
