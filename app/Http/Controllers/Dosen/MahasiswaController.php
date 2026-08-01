<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AssessmentComponent;
use App\Models\Guidance;
use App\Models\InternshipReport;
use App\Models\LogBook;
use App\Models\Notification;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaController extends Controller
{
    private function getLecturer()
    {
        $lecturer = Auth::user()->lecturer;
        if (!$lecturer) abort(403, 'Akses ditolak.');
        return $lecturer;
    }

    private function getInternship(Student $student, $lecturer)
    {
        $internship = $student->internships()
            ->where('lecturer_id', $lecturer->id)
            ->with('company')
            ->first();

        if (!$internship) abort(404, 'Data magang tidak ditemukan.');
        return $internship;
    }

    /**
     * Magang yang sudah ditutup Kaprodi adalah jejak akademik yang sah: catatan
     * logbook maupun nilainya tak boleh berubah lagi. Bila memang perlu
     * dikoreksi, Kaprodi membuka kembali status selesainya lebih dulu — jadi
     * kunci ini tetap punya jalan keluar, bukan jalan buntu.
     *
     * @return \Illuminate\Http\RedirectResponse|null null bila masih boleh diubah.
     */
    private function tolakBilaSelesai($internship, string $hal = 'catatan')
    {
        if (! $internship->is_finished) {
            return null;
        }

        return back()->with('error',
            "Magang mahasiswa ini sudah ditandai selesai, sehingga {$hal} terkunci. "
            . 'Minta Kaprodi membuka kembali status selesai bila ada yang perlu dikoreksi.')
            ->with('tab', 'logbook');
    }

    public function detail(Request $request, Student $student)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);

        $student->load([
            'user',
            'guidances'  => fn($q) => $q->orderByDesc('updated_at'),
            'report',
        ]);

        $assessments = AssessmentComponent::with(['detailedComponents' => function ($q) use ($internship) {
            $q->with(['scores' => fn($q2) => $q2->where('internship_id', $internship->id)]);
        }])->get();

        $allScores = $assessments->flatMap(fn($c) => $c->detailedComponents)
            ->flatMap(fn($d) => $d->scores)
            ->pluck('score')
            ->filter();

        $overallAvg = $allScores->count() > 0 ? round($allScores->avg(), 2) : null;

        $filter = $request->input('filter', 'semua');
        $period = $request->input('period', 'semua');

        $logQuery = $student->logBooks();

        if ($filter === 'belum') {
            $logQuery->whereNull('lecturer_note');
        } elseif ($filter === 'sudah') {
            $logQuery->whereNotNull('lecturer_note');
        }

        if ($period === '7hari') {
            $logQuery->where('date', '>=', now()->subDays(7));
        } elseif ($period === '30hari') {
            $logQuery->where('date', '>=', now()->subDays(30));
        } elseif ($period === 'bulan_ini') {
            $logQuery->whereMonth('date', now()->month)->whereYear('date', now()->year);
        }

        if ($filter === 'semua') {
            $logQuery->orderByRaw('lecturer_note IS NOT NULL')->orderByDesc('updated_at');
        } else {
            $logQuery->orderByDesc('updated_at');
        }

        $logBooks = $logQuery->get();

        $totalLog     = $student->logBooks()->count();
        $sudahDicatat = $student->logBooks()->whereNotNull('lecturer_note')->count();

        return view('dosen.mahasiswa.detail', compact(
            'student', 'internship', 'assessments', 'overallAvg',
            'logBooks', 'filter', 'period', 'totalLog', 'sudahDicatat'
        ));
    }

    public function approveBimbingan(Request $request, Student $student, Guidance $guidance)
    {
        $lecturer = $this->getLecturer();
        $this->getInternship($student, $lecturer);
        abort_unless($guidance->student_id === $student->id, 404);

        $guidance->update([
            'status'       => 'approved',
            'lecturer_note' => $request->input('note'),
        ]);

        Notification::kirim($student->user_id, "Bimbingan \"{$guidance->title}\" disetujui dosen pembimbing.", 'bimbingan', $request->input('note'), '/mahasiswa/bimbingan');

        return back()->with('success', 'Bimbingan berhasil disetujui.');
    }

    public function revisiBimbingan(Request $request, Student $student, Guidance $guidance)
    {
        $lecturer = $this->getLecturer();
        $this->getInternship($student, $lecturer);
        abort_unless($guidance->student_id === $student->id, 404);

        $request->validate(['note' => 'required|string'], [
            'note.required' => 'Catatan revisi wajib diisi.',
        ]);

        $guidance->update([
            'status'       => 'rejected',
            'lecturer_note' => $request->note,
        ]);

        Notification::kirim($student->user_id, "Bimbingan \"{$guidance->title}\" perlu direvisi.", 'bimbingan', $request->note, '/mahasiswa/bimbingan');

        return back()->with('success', 'Bimbingan ditandai untuk revisi.');
    }

    public function approveLaporan(Request $request, Student $student, InternshipReport $report)
    {
        $lecturer = $this->getLecturer();
        $this->getInternship($student, $lecturer);

        if ($report->student_id !== $student->id) abort(404);

        $report->update([
            'status'        => 'approved',
            'lecturer_note' => $request->input('note'),
            'reviewed_at'   => now(),
        ]);

        Notification::kirim($student->user_id, 'Laporan akhir magang disetujui dosen pembimbing.', 'laporan', $request->input('note'), '/mahasiswa/laporan');

        return back()->with('success', 'Laporan akhir berhasil disetujui.');
    }

    public function revisiLaporan(Request $request, Student $student, InternshipReport $report)
    {
        $lecturer = $this->getLecturer();
        $this->getInternship($student, $lecturer);

        if ($report->student_id !== $student->id) abort(404);

        $request->validate(['note' => 'required|string'], [
            'note.required' => 'Catatan revisi wajib diisi.',
        ]);

        $report->update([
            'status'        => 'rejected',
            'lecturer_note' => $request->note,
            'reviewed_at'   => now(),
        ]);

        Notification::kirim($student->user_id, 'Laporan akhir magang perlu direvisi.', 'laporan', $request->note, '/mahasiswa/laporan');

        return back()->with('success', 'Laporan ditandai untuk revisi.');
    }

    public function logBookNote(Request $request, Student $student, LogBook $logBook)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);
        abort_unless($logBook->student_id === $student->id, 404);

        if ($terkunci = $this->tolakBilaSelesai($internship)) {
            return $terkunci;
        }

        $request->validate(['note' => 'required|string|max:1000'], [
            'note.required' => 'Catatan tidak boleh kosong.',
        ]);

        $logBook->update(['lecturer_note' => $request->input('note')]);

        return back()->with('success', 'Catatan logbook berhasil disimpan.')->with('tab', 'logbook');
    }

    public function hapusLogBookNote(Student $student, LogBook $logBook)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);
        abort_unless($logBook->student_id === $student->id, 404);

        if ($terkunci = $this->tolakBilaSelesai($internship)) {
            return $terkunci;
        }

        $logBook->update(['lecturer_note' => null]);

        return back()->with('success', 'Catatan logbook berhasil dihapus.')->with('tab', 'logbook');
    }

    public function nilaiPage(Student $student)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);
        $student->load('user');

        $components = AssessmentComponent::forScorer('lecturer')->with(['detailedComponents' => function ($q) use ($internship) {
            $q->with(['scores' => fn($q2) => $q2->where('internship_id', $internship->id)
                ->where('scorer_type', 'lecturer')]);
        }])->get();

        return view('dosen.mahasiswa.nilai', compact('student', 'internship', 'components'));
    }

    public function updateNilai(Request $request, Student $student)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);

        // Magang yang sudah ditutup Kaprodi = jejak akademik yang sah; nilainya
        // tak boleh berubah lagi. Bila memang perlu dikoreksi, Kaprodi membuka
        // kembali status selesainya lebih dulu.
        if ($internship->is_finished) {
            return back()->with('error',
                'Magang mahasiswa ini sudah ditandai selesai, sehingga nilainya terkunci. Minta Kaprodi membuka kembali status selesai bila ada nilai yang perlu dikoreksi.');
        }

        $request->validate([
            'scores'   => 'required|array',
            'scores.*' => 'nullable|numeric|min:1|max:10',
        ]);

        foreach ($request->scores as $detailId => $score) {
            if ($score !== null && $score !== '') {
                StudentScore::updateOrCreate(
                    [
                        'internship_id'                    => $internship->id,
                        'detailed_assessment_component_id' => $detailId,
                        'scorer_type'                       => 'lecturer',
                    ],
                    ['score' => $score]
                );
            }
        }

        return back()->with('success', 'Nilai berhasil diperbarui.');
    }
}
