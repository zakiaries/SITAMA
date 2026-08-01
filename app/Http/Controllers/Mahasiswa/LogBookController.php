<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\MengunciSaatSelesaiMagang;
use App\Http\Controllers\Controller;
use App\Models\LogBook;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogBookController extends Controller
{
    use MengunciSaatSelesaiMagang;

    public function index(Request $request)
    {
        $student = Auth::user()->student;
        // Terbaru ditambah/diedit di paling atas (updated_at ikut berubah saat edit).
        $query   = $student->logBooks()->orderByDesc('updated_at')->orderByDesc('id');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $logBooks = $query->get();

        // Logbook = catatan kegiatan magang → hanya bisa diisi saat magang aktif
        // DAN belum diajukan selesai (lihat Internship::terkunciUntukMahasiswa).
        $internship  = $student->activeInternship()->first();
        $terkunci    = $internship?->alasanTerkunci();
        $canFill     = $internship && ! $terkunci;
        $noLecturer  = $canFill && ! $internship->lecturer_id && ! $student->lecturer_id;

        return view('mahasiswa.logbook.index', compact('logBooks', 'canFill', 'noLecturer', 'terkunci'));
    }

    public function store(Request $request)
    {
        $student = Auth::user()->student;

        // Tanpa magang aktif, logbook tak punya konteks & tak terlihat pembimbing.
        if (! $student->activeInternship()->exists()) {
            return back()->with('error',
                'Kamu belum memiliki magang aktif. Log book bisa diisi setelah pengajuan magangmu disetujui Kaprodi.');
        }

        if ($terkunci = $this->tolakBilaTerkunci($student)) {
            return $terkunci;
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
        $student = Auth::user()->student;

        if ($logBook->student_id !== $student->id) {
            abort(403);
        }

        if ($terkunci = $this->tolakBilaTerkunci($student)) {
            return $terkunci;
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

        // Tujuan klik berbeda per peran: masing-masing dibawa ke halaman detail
        // mahasiswa di portalnya sendiri, langsung ke logbook yang dimaksud.
        Notification::kirim(
            $internship->lecturer?->user?->id, $message, 'log_book', $detail,
            "/dosen/mahasiswa/{$student->id}#logbook-{$logBook->id}"
        );

        Notification::kirim(
            $internship->lecturerIndustry?->user?->id, $message, 'log_book', $detail,
            "/dosen-industri/mahasiswa/{$student->id}#logbook-{$logBook->id}"
        );
    }

    public function destroy(LogBook $logBook)
    {
        $student = Auth::user()->student;

        if ($logBook->student_id !== $student->id) {
            abort(403);
        }

        if ($terkunci = $this->tolakBilaTerkunci($student)) {
            return $terkunci;
        }

        $logBook->delete();
        return redirect()->route('mahasiswa.logbook')
            ->with('success', 'Log book berhasil dihapus.');
    }
}
