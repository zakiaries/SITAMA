<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Concerns\MengunciSaatSelesaiMagang;
use App\Models\LogBook;
use App\Models\Notification;
use Illuminate\Http\Request;

class LogBookController extends ApiController
{
    use MengunciSaatSelesaiMagang;

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

        // Sama seperti web: logbook hanya untuk mahasiswa dengan magang aktif.
        if (! $student->activeInternship()->exists()) {
            return response()->json(['message' => 'Kamu belum memiliki magang aktif. Log book bisa diisi setelah pengajuan magangmu disetujui Kaprodi.'], 422);
        }

        if ($terkunci = $this->tolakBilaTerkunciJson($student)) {
            return $terkunci;
        }

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

        if ($terkunci = $this->tolakBilaTerkunciJson($student)) {
            return $terkunci;
        }

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

        if ($terkunci = $this->tolakBilaTerkunciJson($student)) {
            return $terkunci;
        }

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

        // Tujuannya beda per peran, jadi tak bisa disatukan dalam satu perulangan:
        // masing-masing diarahkan ke logbook yang sama di portalnya sendiri.
        Notification::kirim(
            $internship->lecturer?->user?->id, $message, 'log_book', $detail,
            "/dosen/mahasiswa/{$student->id}#logbook-{$logBook->id}"
        );

        Notification::kirim(
            $internship->lecturerIndustry?->user?->id, $message, 'log_book', $detail,
            "/dosen-industri/mahasiswa/{$student->id}#logbook-{$logBook->id}"
        );
    }
}
