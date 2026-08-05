<?php

namespace App\Http\Controllers\Kaprodi;

use App\Exports\MagangExport;
use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\LogBook;
use App\Models\Period;
use App\Models\Seminar;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Penyaring periode menggantikan penyaring "tahun akademik" yang lama.
        // Yang lama membaca students.academic_year — teks bebas yang diketik
        // sendiri mahasiswa — sehingga dropdown-nya berisi nilai seperti
        // "2023/2026" dan angkanya tak bisa dipercaya.
        $periode      = $request->input('periode') ?: Period::pilihanBawaan();
        $periodeList  = Period::terbaru()->get();
        $tanpaPeriode = Student::whereNull('period_id')->count();

        // Base scopes
        $studentBase = Period::terapkan(Student::where('status', 'active'), $periode);

        $internshipBase = Internship::when(
            Period::menyaring($periode),
            fn($q) => $q->whereHas('student', fn($s) => Period::terapkan($s, $periode))
        );

        // ── Stat cards ──────────────────────────────────────────────
        $totalMahasiswa = (clone $studentBase)->count();

        // Pendaftar baru SENGAJA tidak ikut disaring: ia belum diterima ke
        // angkatan mana pun — persetujuan Kaprodi-lah yang menempatkannya.
        // Menyaringnya berarti menyembunyikan justru orang yang butuh tindakan.
        $pendingMahasiswa = Student::where('status', 'pending')->count();
        $belumMagang      = (clone $studentBase)->whereDoesntHave('internships')->count();
        $aktif            = (clone $studentBase)->whereHas('internships', fn($q) => $q->where('is_finished', false))->count();
        $selesai          = (clone $studentBase)->whereHas('internships', fn($q) => $q->where('is_finished', true))->count();
        $totalDosen       = Lecturer::whereHas('user', fn($q) => $q->where('role', 'lecturer'))->count();
        $totalSeminar     = Seminar::count();

        // ── Chart 1: Status mahasiswa (donut) ───────────────────────
        $chartStatus = [
            'labels' => ['Belum Magang', 'Aktif Magang', 'Selesai'],
            'data'   => [$belumMagang, $aktif, $selesai],
        ];

        // ── Chart 2: Top perusahaan (bar) ───────────────────────────
        $topCompanies = (clone $internshipBase)
            ->join('companies', 'internships.company_id', '=', 'companies.id')
            ->select('companies.name', DB::raw('count(*) as total'))
            ->groupBy('companies.name')
            ->orderByDesc('total')
            ->take(8)
            ->pluck('total', 'companies.name');

        $chartCompanies = [
            'labels' => $topCompanies->keys()->toArray(),
            'data'   => $topCompanies->values()->toArray(),
        ];

        // ── Chart 3: Distribusi per prodi (bar) ──────────────────────
        $perProdi = (clone $studentBase)
            ->select('study_program', DB::raw('count(*) as total'))
            ->groupBy('study_program')
            ->orderByDesc('total')
            ->pluck('total', 'study_program');

        $chartProdi = [
            'labels' => $perProdi->keys()->map(fn($k) => $k ?: 'Tidak diketahui')->toArray(),
            'data'   => $perProdi->values()->toArray(),
        ];

        // ── Chart 4: Progress logbook ─────────────────────────────────
        $logbookCounts = DB::table('log_books')
            ->whereIn('student_id', (clone $studentBase)->whereHas('internships')->pluck('id'))
            ->select('student_id', DB::raw('count(*) as total'))
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $totalMagang   = $aktif + $selesai;
        $logbookTerpenuhi = $logbookCounts->filter(fn($c) => $c >= Internship::MIN_LOGBOOK)->count();
        $logbookBelum     = $totalMagang - $logbookTerpenuhi;

        $chartLogbook = [
            'labels' => ['Terpenuhi (≥' . Internship::MIN_LOGBOOK . ')', 'Belum Terpenuhi'],
            'data'   => [$logbookTerpenuhi, max(0, $logbookBelum)],
        ];

        // ── Chart 5: Status nilai (bar grouped) ──────────────────────
        $internshipIdList = (clone $internshipBase)->pluck('internships.id');

        $nilaiKampus   = StudentScore::whereIn('internship_id', $internshipIdList)
            ->where('scorer_type', 'lecturer')->distinct('internship_id')->count('internship_id');
        $nilaiIndustri = StudentScore::whereIn('internship_id', $internshipIdList)
            ->where('scorer_type', 'lecturer_industry')->distinct('internship_id')->count('internship_id');
        $nilaiKeduanya = StudentScore::whereIn('internship_id', $internshipIdList)
            ->where('scorer_type', 'lecturer')->distinct('internship_id')
            ->whereIn('internship_id', StudentScore::whereIn('internship_id', $internshipIdList)
                ->where('scorer_type', 'lecturer_industry')->select('internship_id'))
            ->count('internship_id');
        $nilaiTidakAda = $totalMagang - max($nilaiKampus, $nilaiIndustri);

        $chartNilai = [
            'labels' => ['Dinilai Dosen Kampus', 'Dinilai Pembimbing Industri', 'Keduanya', 'Belum Dinilai'],
            'data'   => [$nilaiKampus, $nilaiIndustri, $nilaiKeduanya, max(0, $nilaiTidakAda)],
        ];

        // ── Pending list ──────────────────────────────────────────────
        $pendingList = Student::with('user')->where('status', 'pending')->latest()->take(3)->get();

        return view('kaprodi.dashboard.index', compact(
            'user', 'periode', 'periodeList', 'tanpaPeriode',
            'totalMahasiswa', 'pendingMahasiswa', 'belumMagang', 'aktif', 'selesai',
            'totalDosen', 'totalSeminar', 'pendingList',
            'chartStatus', 'chartCompanies', 'chartProdi', 'chartLogbook', 'chartNilai'
        ));
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode') ?: Period::pilihanBawaan();
        $label   = Period::labelPilihan($periode);

        // Periodenya masuk nama berkas supaya rekap dua angkatan tak tertukar
        // saat sama-sama tersimpan di folder unduhan.
        $slug = Str::slug($label);

        return Excel::download(
            new MagangExport($periode),
            "rekap-magang-{$slug}-" . now()->format('Ymd') . '.xlsx'
        );
    }
}
