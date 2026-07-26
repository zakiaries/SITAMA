<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use App\Models\SeminarPresenter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sisi mahasiswa untuk model seminar sesi-grup.
 *
 * Mahasiswa tidak lagi mengajukan seminar sendiri. Dosen pembimbing membuat sesi
 * dan menetapkan mahasiswa sebagai penyaji; mahasiswa mengisi ketersediaan tanggal,
 * lalu melihat jadwal final + QR daftar hadir.
 */
class SeminarController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        $presenterRows = $student
            ? SeminarPresenter::where('student_id', $student->id)
                ->with(['seminar.lecturer.user', 'seminar.attendances'])
                ->get()
                ->sortByDesc(fn ($p) => $p->seminar->created_at)
                ->values()
            : collect();

        return view('mahasiswa.seminar.index', compact('presenterRows'));
    }

    /** Isi ketersediaan tanggal (hanya saat sesi masih draft). */
    public function submitAvailability(Request $request, Seminar $seminar)
    {
        $student = Auth::user()->student;
        $row = SeminarPresenter::where('seminar_id', $seminar->id)
            ->where('student_id', $student?->id)->first();
        abort_unless($row, 404);

        if ($seminar->status !== 'draft') {
            return back()->with('error', 'Jadwal sudah ditetapkan, ketersediaan tidak dapat diubah.');
        }

        $request->validate([
            'available_dates' => 'required|string|max:255',
        ], [
            'available_dates.required' => 'Isi tanggal yang kamu bisa.',
        ]);

        $row->update([
            'available_dates' => $request->available_dates,
            'responded_at'    => now(),
        ]);

        return back()->with('success', 'Ketersediaan tanggalmu tersimpan.');
    }

    public function detail(Seminar $seminar)
    {
        $student = Auth::user()->student;
        abort_unless($this->isPresenter($seminar, $student?->id), 403);

        $seminar->load(['lecturer.user', 'presenters.student.user', 'attendances.student.user']);

        // QR daftar hadir kini ditampilkan dosen (rotating, anti titip-absen), bukan statis di sini.
        return view('mahasiswa.seminar.detail', compact('seminar'));
    }

    public function beritaAcaraPdf(Seminar $seminar)
    {
        $student = Auth::user()->student;
        abort_unless($this->isPresenter($seminar, $student?->id), 403);

        $seminar->load(['lecturer.user', 'presenters.student.user', 'attendances' => fn ($q) => $q->orderBy('created_at')]);

        $pdf = Pdf::loadView('mahasiswa.seminar.berita-acara-pdf', compact('seminar'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('berita-acara-seminar-' . $seminar->id . '.pdf');
    }

    private function isPresenter(Seminar $seminar, $studentId): bool
    {
        return $studentId && SeminarPresenter::where('seminar_id', $seminar->id)
            ->where('student_id', $studentId)->exists();
    }
}
