<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\StudentScore;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SeminarController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        // Seminar umum/wajib (tidak terikat ke mahasiswa tertentu)
        $seminars = Seminar::whereNull('student_id')->orderByDesc('date')->get();

        // Jadwal seminar yang diajukan mahasiswa ini sendiri
        $mySeminars = $student
            ? Seminar::where('student_id', $student->id)->with('attendances')->orderByDesc('date')->get()
            : collect();

        $requirements = $this->seminarRequirements($student);
        $canSubmit    = $this->canSubmit($student);

        return view('mahasiswa.seminar.index', compact(
            'seminars', 'mySeminars', 'requirements', 'canSubmit'
        ));
    }

    /** Gate pengajuan seminar = magang sudah selesai (di-ACC Kaprodi). */
    private function canSubmit($student): bool
    {
        $internship = $student?->activeInternship()->first();
        return (bool) ($internship?->is_finished);
    }

    /**
     * Rincian syarat kelayakan seminar (untuk transparansi ke mahasiswa).
     * Semua item ini otomatis terpenuhi begitu magang di-ACC selesai oleh Kaprodi,
     * karena mereka adalah syarat pengajuan selesai magang (lihat MagangSayaController).
     */
    private function seminarRequirements($student): array
    {
        $internship   = $student?->activeInternship()->first();
        $report       = $student?->report;
        $logbookCount = $student ? $student->logBooks()->count() : 0;

        $hasLecturerScore = $internship && StudentScore::where('internship_id', $internship->id)
            ->where('scorer_type', 'lecturer')->exists();
        $hasIndustryScore = $internship && StudentScore::where('internship_id', $internship->id)
            ->where('scorer_type', 'lecturer_industry')->exists();

        return [
            [
                'label' => 'Magang sudah ditandai selesai oleh Kaprodi',
                'met'   => (bool) ($internship?->is_finished),
                'hint'  => 'Ajukan selesai magang di halaman Magang Saya dan tunggu ACC Kaprodi.',
            ],
            [
                'label' => 'Sertifikat magang sudah diunggah',
                'met'   => (bool) ($internship?->certificate_path),
                'hint'  => 'Unggah sertifikat magang di halaman Magang Saya.',
            ],
            [
                'label' => 'Laporan akhir sudah di-ACC dosen pembimbing',
                'met'   => $report && $report->status === 'approved',
                'hint'  => 'Unggah laporan akhir dan tunggu ACC di menu Laporan Akhir.',
            ],
            [
                'label' => 'Nilai dari dosen pembimbing (kampus) sudah diisi',
                'met'   => $hasLecturerScore,
                'hint'  => 'Nilai belum diinput oleh dosen pembimbing kampus.',
            ],
            [
                'label' => 'Nilai dari pembimbing industri sudah diisi',
                'met'   => $hasIndustryScore,
                'hint'  => 'Nilai belum diinput oleh pembimbing industri.',
            ],
            [
                'label' => 'Minimal ' . Internship::MIN_LOGBOOK . ' logbook sudah diisi',
                'met'   => $logbookCount >= Internship::MIN_LOGBOOK,
                'hint'  => "Baru ada {$logbookCount} logbook, minimal " . Internship::MIN_LOGBOOK . '.',
            ],
        ];
    }

    public function store(Request $request)
    {
        $student = Auth::user()->student;

        if (!$this->canSubmit($student)) {
            return back()->with('error', 'Anda belum memenuhi syarat untuk mengajukan jadwal seminar.');
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'date'        => 'required|date|after_or_equal:today',
            'time'        => 'nullable|string|max:50',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ], [
            'date.after_or_equal' => 'Tanggal seminar tidak boleh sebelum hari ini.',
        ]);

        Seminar::create([
            'title'       => $request->title,
            'program'     => $student->study_program ?: 'Magang',
            'date'        => $request->date,
            'time'        => $request->time,
            'location'    => $request->location,
            'organizer'   => Auth::user()->name,
            'description' => $request->description,
            'status'      => 'pending',
            'student_id'  => $student->id,
        ]);

        return redirect()->route('mahasiswa.seminar')
            ->with('success', 'Jadwal seminar berhasil diajukan. Menunggu persetujuan Kaprodi.');
    }

    public function update(Request $request, Seminar $seminar)
    {
        $this->authorizeOwnSeminar($seminar);

        // Hanya boleh diubah saat masih menunggu atau setelah ditolak (ajukan ulang jadwal).
        if (!in_array($seminar->status, ['pending', 'rejected'], true)) {
            return back()->with('error', 'Seminar yang sudah disetujui tidak dapat diubah.');
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'date'        => 'required|date|after_or_equal:today',
            'time'        => 'nullable|string|max:50',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ], [
            'date.after_or_equal' => 'Tanggal seminar tidak boleh sebelum hari ini.',
        ]);

        // Mengajukan ulang -> kembali ke status menunggu & bersihkan alasan penolakan.
        $seminar->update([
            'title'            => $request->title,
            'date'             => $request->date,
            'time'             => $request->time,
            'location'         => $request->location,
            'description'      => $request->description,
            'status'           => 'pending',
            'rejection_reason' => null,
        ]);

        return redirect()->route('mahasiswa.seminar')
            ->with('success', 'Jadwal seminar berhasil diperbarui. Menunggu persetujuan Kaprodi.');
    }

    public function destroy(Seminar $seminar)
    {
        $this->authorizeOwnSeminar($seminar);

        if (!in_array($seminar->status, ['pending', 'rejected'], true)) {
            return back()->with('error', 'Seminar yang sudah disetujui tidak dapat dibatalkan.');
        }

        $seminar->registrations()->delete();
        $seminar->attendances()->delete();
        $seminar->delete();

        return redirect()->route('mahasiswa.seminar')
            ->with('success', 'Pengajuan seminar dibatalkan.');
    }

    private function authorizeOwnSeminar(Seminar $seminar): void
    {
        $student = Auth::user()->student;
        if (!$student || $seminar->student_id !== $student->id) abort(403);
    }

    public function detail(Seminar $seminar)
    {
        $student = Auth::user()->student;

        $seminar->load([
            'registrations.student.user',
            'attendances' => fn($q) => $q->latest(),
            'student.user',
        ]);

        $isOwner = $student && $seminar->student_id === $student->id;

        // QR hanya aktif untuk seminar milik sendiri yang sudah disetujui.
        $qrUrl = null;
        $qrSvg = null;
        if ($isOwner && $seminar->status === 'scheduled' && $seminar->access_token) {
            $qrUrl = url('/seminar/hadir/' . $seminar->access_token);
            $qrSvg = QrCode::format('svg')->size(220)->margin(1)->generate($qrUrl);
        }

        // Untuk seminar umum (bukan milik mahasiswa): status pendaftaran audiens.
        $isRegistered = $student
            ? SeminarRegistration::where('student_id', $student->id)
                ->where('seminar_id', $seminar->id)->exists()
            : false;

        return view('mahasiswa.seminar.detail', compact('seminar', 'isOwner', 'qrUrl', 'qrSvg', 'isRegistered'));
    }

    public function register(Seminar $seminar)
    {
        $student = Auth::user()->student;

        SeminarRegistration::firstOrCreate(
            ['student_id' => $student->id, 'seminar_id' => $seminar->id],
            ['status' => 'registered']
        );

        return back()->with('success', 'Berhasil mendaftar seminar!');
    }

    /** Cetak berita acara (daftar hadir tamu) seminar ke PDF. */
    public function beritaAcaraPdf(Seminar $seminar)
    {
        $this->authorizeOwnSeminar($seminar);

        $seminar->load(['attendances' => fn($q) => $q->orderBy('created_at'), 'student.user']);

        // Sematkan tanda tangan sebagai data-URI agar dompdf tidak perlu akses file/URL.
        $attendances = $seminar->attendances->map(function ($a) {
            $a->signature_data = null;
            if ($a->signature_path && Storage::disk('public')->exists($a->signature_path)) {
                $a->signature_data = 'data:image/png;base64,'
                    . base64_encode(Storage::disk('public')->get($a->signature_path));
            }
            return $a;
        });

        $pdf = Pdf::loadView('mahasiswa.seminar.berita-acara-pdf', [
            'seminar'     => $seminar,
            'attendances' => $attendances,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('berita-acara-seminar-' . $seminar->id . '.pdf');
    }
}
