<?php

namespace App\Http\Controllers\DosenIndustri;

use App\Http\Controllers\Controller;
use App\Models\AssessmentComponent;
use App\Models\Internship;
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
            ->where('lecturer_industry_id', $lecturer->id)
            ->with('company')
            ->first();

        if (!$internship) abort(404, 'Data magang tidak ditemukan.');
        return $internship;
    }

    /**
     * Lihat catatan di Dosen\MahasiswaController::tolakBilaSelesai — magang yang
     * sudah ditutup Kaprodi terkunci, termasuk komentar logbooknya.
     *
     * @return \Illuminate\Http\RedirectResponse|null null bila masih boleh diubah.
     */
    private function tolakBilaSelesai($internship, string $hal = 'komentar')
    {
        if (! $internship->is_finished) {
            return null;
        }

        return back()->with('error',
            "Magang mahasiswa ini sudah ditandai selesai, sehingga {$hal} terkunci. "
            . 'Minta Kaprodi membuka kembali status selesai bila ada yang perlu dikoreksi.');
    }

    public function detail(Request $request, Student $student)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);
        // `report` dimuat untuk tautan berkas — gerbang aksesnya sudah lama
        // mengizinkan peran ini, hanya tautannya yang belum pernah ada.
        $student->load(['user', 'report']);

        $filter = $request->input('filter', 'semua');
        $period = $request->input('period', 'semua');

        $query = $student->logBooks();

        if ($filter === 'belum') {
            $query->whereNull('industry_note');
        } elseif ($filter === 'sudah') {
            $query->whereNotNull('industry_note');
        }

        if ($period === '7hari') {
            $query->where('date', '>=', now()->subDays(7));
        } elseif ($period === '30hari') {
            $query->where('date', '>=', now()->subDays(30));
        } elseif ($period === 'bulan_ini') {
            $query->whereMonth('date', now()->month)->whereYear('date', now()->year);
        }

        if ($filter === 'semua') {
            $query->orderByRaw('industry_note IS NOT NULL')->orderByDesc('updated_at');
        } else {
            $query->orderByDesc('updated_at');
        }

        $logBooks = $query->get();

        $totalLog     = $student->logBooks()->count();
        $sudahDikomen = $student->logBooks()->whereNotNull('industry_note')->count();

        // Konteks yang dibutuhkan pembimbing industri tapi dulu tak ada di halaman ini:
        // kontak mahasiswa, data akademiknya, dan siapa dosen pembimbing kampusnya
        // (untuk koordinasi), plus progres & status penilaian yang jadi tugasnya.
        $internship->load('lecturer.user');

        $minLogbook    = Internship::MIN_LOGBOOK;
        $logTerakhir   = $student->logBooks()->max('date');
        $nilaiIndustri = $internship->nilaiSummary()['industry'];
        $komponenTotal = count($nilaiIndustri['components']);
        $komponenDinilai = collect($nilaiIndustri['components'])
            ->filter(fn ($c) => $c['avg'] !== null)
            ->count();

        return view('dosen-industri.mahasiswa.detail', compact(
            'student', 'internship', 'logBooks', 'filter', 'period', 'totalLog', 'sudahDikomen',
            'minLogbook', 'logTerakhir', 'nilaiIndustri', 'komponenTotal', 'komponenDinilai'
        ));
    }

    public function kirimKomentar(Request $request, Student $student, LogBook $logBook)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);
        abort_unless($logBook->student_id === $student->id, 404);

        if ($terkunci = $this->tolakBilaSelesai($internship)) {
            return $terkunci;
        }

        $request->validate(['komentar' => 'required|string|max:1000'], [
            'komentar.required' => 'Komentar tidak boleh kosong.',
        ]);

        $logBook->update(['industry_note' => $request->komentar]);

        Notification::kirim($student->user_id, "Pembimbing industri mengomentari logbook \"{$logBook->title}\".", 'log_book', $request->komentar, '/mahasiswa/logbook');

        return back()->with('success', 'Komentar berhasil dikirim.');
    }

    public function hapusKomentar(Student $student, LogBook $logBook)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);
        abort_unless($logBook->student_id === $student->id, 404);

        if ($terkunci = $this->tolakBilaSelesai($internship)) {
            return $terkunci;
        }

        $logBook->update(['industry_note' => null]);

        return back()->with('success', 'Komentar berhasil dihapus.');
    }

    public function penilaianPage(Student $student)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);
        $student->load('user');

        $components = AssessmentComponent::forScorer('lecturer_industry')->with(['detailedComponents' => function ($q) use ($internship) {
            $q->with(['scores' => fn($q2) => $q2->where('internship_id', $internship->id)
                ->where('scorer_type', 'lecturer_industry')]);
        }])->get();

        $belumSiapDinilai = $internship->alasanBelumSiapDinilai();

        return view('dosen-industri.mahasiswa.penilaian', compact(
            'student', 'internship', 'components', 'belumSiapDinilai'
        ));
    }

    public function simpanPenilaian(Request $request, Student $student)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);

        // Lihat catatan di Dosen\MahasiswaController::updateNilai — nilai magang
        // yang sudah ditutup Kaprodi terkunci.
        if ($internship->is_finished) {
            return back()->with('error',
                'Magang mahasiswa ini sudah ditandai selesai, sehingga penilaian terkunci. Minta Kaprodi membuka kembali status selesai bila ada yang perlu dikoreksi.');
        }

        // Sama seperti dosen kampus: mahasiswa harus merampungkan magangnya dulu.
        if ($alasan = $internship->alasanBelumSiapDinilai()) {
            return back()->with('error', $alasan);
        }

        $request->validate([
            'scores'            => 'array',
            'scores.*'          => 'nullable|numeric|min:1|max:10',
            'performance_notes' => 'nullable|string|max:2000',
        ]);

        foreach ((array) $request->scores as $detailId => $score) {
            if ($score !== null && $score !== '') {
                StudentScore::updateOrCreate(
                    [
                        'internship_id'                    => $internship->id,
                        'detailed_assessment_component_id' => $detailId,
                        'scorer_type'                       => 'lecturer_industry',
                    ],
                    ['score' => $score]
                );
            }
        }

        if ($request->filled('performance_notes')) {
            $internship->update([
                'performance_notes'      => $request->performance_notes,
                'performance_notes_by'   => Auth::user()->name,
                'performance_notes_date' => now()->toDateString(),
            ]);
        }

        return back()->with('success', 'Penilaian berhasil disimpan.');
    }
}
