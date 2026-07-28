<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\Notification;
use App\Models\StudentScore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MagangSayaController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;
        $internship = $student?->activeInternship()->with(['company', 'lecturer.user', 'lecturerIndustry.user'])->first();

        $finishChecklist = $internship && !$internship->is_finished
            ? $this->finishChecklist($student, $internship)
            : [];
        $canRequestFinish = !empty($finishChecklist) && !in_array(false, array_column($finishChecklist, 'met'), true);

        $logBooks = $student->logBooks()->orderByDesc('updated_at')->limit(5)->get();

        return view('mahasiswa.magang-saya.index', compact(
            'internship', 'logBooks', 'finishChecklist', 'canRequestFinish'
        ));
    }

    public function uploadCertificate(Request $request)
    {
        $student    = Auth::user()->student;
        $internship = $student->activeInternship()->first();

        if (!$internship) {
            return back()->with('error', 'Belum ada data magang aktif.');
        }

        $request->validate([
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'certificate.required' => 'File sertifikat wajib diunggah.',
            'certificate.mimes'    => 'Sertifikat harus berformat PDF atau gambar (JPG/PNG).',
            'certificate.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        if ($internship->certificate_path) {
            Storage::disk('public')->delete($internship->certificate_path);
        }

        $internship->update([
            'certificate_path' => $request->file('certificate')->store('certificates', 'public'),
        ]);

        return back()->with('success', 'Sertifikat magang berhasil diunggah.');
    }

    public function requestFinish()
    {
        $student    = Auth::user()->student;
        $internship = $student->activeInternship()->first();

        if (!$internship || $internship->is_finished) {
            return back()->with('error', 'Tidak ada magang aktif yang bisa diajukan selesai.');
        }

        if ($internship->finish_requested) {
            return back()->with('error', 'Pengajuan selesai magang sudah dikirim, tunggu ACC Kaprodi.');
        }

        $checklist = $this->finishChecklist($student, $internship);
        if (in_array(false, array_column($checklist, 'met'), true)) {
            return back()->with('error', 'Belum semua syarat terpenuhi untuk mengajukan selesai magang.');
        }

        $internship->update(['finish_requested' => true]);

        // Beri tahu Kaprodi ada pengajuan selesai magang yang perlu di-ACC.
        foreach (User::where('role', 'kaprodi')->pluck('id') as $kaprodiId) {
            Notification::create([
                'user_id'     => $kaprodiId,
                'message'     => 'Pengajuan selesai magang dari ' . Auth::user()->name,
                'date'        => now()->toDateString(),
                'category'    => 'selesai_magang',
                'is_read'     => false,
                'detail_text' => 'Perusahaan: ' . (optional($internship->company)->name ?? '-') . '. Menunggu ACC Kaprodi.',
            ]);
        }

        return back()->with('success', 'Pengajuan selesai magang berhasil dikirim. Menunggu ACC Kaprodi.');
    }

    private function finishChecklist($student, $internship): array
    {
        $report       = $student->report;
        $logbookCount = $student->logBooks()->count();

        $hasLecturerScore  = StudentScore::where('internship_id', $internship->id)
            ->where('scorer_type', 'lecturer')->exists();
        $hasIndustryScore  = StudentScore::where('internship_id', $internship->id)
            ->where('scorer_type', 'lecturer_industry')->exists();

        return [
            [
                'label' => 'Sertifikat magang sudah diunggah',
                'met'   => (bool) $internship->certificate_path,
                'hint'  => 'Unggah sertifikat magang di atas.',
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
}
