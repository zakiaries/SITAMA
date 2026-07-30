<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\LogBook;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogBookController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        // Terbaru ditambah/diedit di paling atas (updated_at ikut berubah saat edit).
        $query   = $student->logBooks()->orderByDesc('updated_at')->orderByDesc('id');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $logBooks = $query->get();

        // Logbook = catatan kegiatan magang → hanya bisa diisi saat magang aktif.
        $internship  = $student->activeInternship()->first();
        $canFill     = (bool) $internship;
        $noLecturer  = $canFill && ! $internship->lecturer_id && ! $student->lecturer_id;

        return view('mahasiswa.logbook.index', compact('logBooks', 'canFill', 'noLecturer'));
    }

    public function store(Request $request)
    {
        $student = Auth::user()->student;

        // Tanpa magang aktif, logbook tak punya konteks & tak terlihat pembimbing.
        if (! $student->activeInternship()->exists()) {
            return back()->with('error',
                'Kamu belum memiliki magang aktif. Log book bisa diisi setelah pengajuan magangmu disetujui Kaprodi.');
        }

        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date|before_or_equal:today',
        ], [
            'date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
        ]);

        $logBook = LogBook::create([
            'student_id' => $student->id,
            'title'      => $request->title,
            'activity'   => $request->activity,
            'date'       => $request->date,
        ]);

        $this->notifySupervisors($student, $logBook);

        return redirect()->route('mahasiswa.logbook')
            ->with('success', 'Log book berhasil ditambahkan.');
    }

    public function update(Request $request, LogBook $logBook)
    {
        if ($logBook->student_id !== Auth::user()->student->id) {
            abort(403);
        }

        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date|before_or_equal:today',
        ], [
            'date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
        ]);

        $logBook->update([
            'title'    => $request->title,
            'activity' => $request->activity,
            'date'     => $request->date,
        ]);

        return redirect()->route('mahasiswa.logbook')
            ->with('success', 'Log book berhasil diperbarui.');
    }

    private function notifySupervisors($student, LogBook $logBook): void
    {
        $internship = $student->activeInternship()->with(['lecturer.user', 'lecturerIndustry.user'])->first();

        if (!$internship) {
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

    public function destroy(LogBook $logBook)
    {
        if ($logBook->student_id !== Auth::user()->student->id) {
            abort(403);
        }
        $logBook->delete();
        return redirect()->route('mahasiswa.logbook')
            ->with('success', 'Log book berhasil dihapus.');
    }
}
