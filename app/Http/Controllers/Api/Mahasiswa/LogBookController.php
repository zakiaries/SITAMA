<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use App\Models\LogBook;
use App\Models\Notification;
use Illuminate\Http\Request;

class LogBookController extends ApiController
{
    public function index(Request $request)
    {
        $student = $this->currentStudent($request);
        // Terbaru ditambah/diedit di paling atas (updated_at ikut berubah saat edit).
        $query   = $student->logBooks()->orderByDesc('updated_at')->orderByDesc('id');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $logBooks = $query->get()->map(fn ($l) => [
            'id'            => $l->id,
            'title'         => $l->title,
            'activity'      => $l->activity,
            'date'          => optional($l->date)->toDateString(),
            'lecturer_note' => $l->lecturer_note,
            'industry_note' => $l->industry_note,
        ]);

        return response()->json(['logbooks' => $logBooks]);
    }

    public function store(Request $request)
    {
        $student = $this->currentStudent($request);

        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date|before_or_equal:today',
        ]);

        $logBook = LogBook::create([
            'student_id' => $student->id,
            'title'      => $request->title,
            'activity'   => $request->activity,
            'date'       => $request->date,
        ]);

        $this->notifySupervisors($student, $logBook);

        return response()->json(['message' => 'Log book berhasil ditambahkan.', 'id' => $logBook->id], 201);
    }

    public function update(Request $request, LogBook $logBook)
    {
        $student = $this->currentStudent($request);
        abort_if($logBook->student_id !== $student->id, 403, 'Akses ditolak.');

        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date|before_or_equal:today',
        ]);

        $logBook->update([
            'title'    => $request->title,
            'activity' => $request->activity,
            'date'     => $request->date,
        ]);

        return response()->json(['message' => 'Log book berhasil diperbarui.']);
    }

    public function destroy(Request $request, LogBook $logBook)
    {
        $student = $this->currentStudent($request);
        abort_if($logBook->student_id !== $student->id, 403, 'Akses ditolak.');

        $logBook->delete();

        return response()->json(['message' => 'Log book berhasil dihapus.']);
    }

    private function notifySupervisors($student, LogBook $logBook): void
    {
        $internship = $student->activeInternship()->with(['lecturer.user', 'lecturerIndustry.user'])->first();
        if (! $internship) {
            return;
        }

        $message = "{$student->user->name} mengisi log book baru: \"{$logBook->title}\".";
        $detail  = "Log book tanggal {$logBook->date->format('d M Y')}: {$logBook->activity}";

        $userIds = collect([
            $internship->lecturer?->user?->id,
            $internship->lecturerIndustry?->user?->id,
        ])->filter()->unique();

        foreach ($userIds as $userId) {
            Notification::create([
                'user_id'     => $userId,
                'message'     => $message,
                'date'        => now()->toDateString(),
                'category'    => 'log_book',
                'is_read'     => false,
                'detail_text' => $detail,
            ]);
        }
    }
}
