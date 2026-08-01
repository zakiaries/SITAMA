<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use App\Models\Company;
use App\Models\CompanyRequest;
use App\Models\Internship;
use App\Models\JobListing;
use App\Models\Notification;
use App\Models\StudentScore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MagangController extends ApiController
{
    /** GET /ajukan-magang */
    public function ajukanIndex(Request $request)
    {
        $student  = $this->currentStudent($request);
        $requests = CompanyRequest::where('student_id', $student->id)->latest()->get();

        return response()->json([
            'has_active_internship' => $student->internships()->where('is_finished', false)->exists(),
            'has_pending'           => $requests->where('status', 'pending')->isNotEmpty(),
            // Flutter: sembunyikan/nonaktifkan form bila false (dospem belum diplot).
            'has_lecturer'          => (bool) $student->lecturer_id,
            'companies'             => Company::orderBy('name')->get(['id', 'name']),
            'bidang_options'        => JobListing::BIDANG_OPTIONS,
            'requests'              => $requests->map(fn ($r) => [
                'id'               => $r->id,
                'company_name'     => $r->company_name,
                'position'         => $r->position,
                'bidang'           => $r->bidang,
                'start_date'       => optional($r->start_date)->toDateString(),
                'status'           => $r->status,
                'rejection_reason' => $r->rejection_reason,
                'proof_url'        => $r->proof_file ? Storage::url($r->proof_file) : null,
                'created_at'       => $r->created_at->toDateTimeString(),
            ]),
        ]);
    }

    /** POST /ajukan-magang */
    public function ajukanStore(Request $request)
    {
        $student = $this->currentStudent($request);

        if ($student->internships()->where('is_finished', false)->exists()) {
            return response()->json(['message' => 'Kamu sudah memiliki magang aktif.'], 422);
        }
        if (CompanyRequest::where('student_id', $student->id)->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'Kamu sudah memiliki pengajuan yang sedang menunggu review Kaprodi.'], 422);
        }
        // Paritas dengan web: tanpa dospem, magang yang disetujui lahir tanpa
        // dosen dan mahasiswa tak terlihat di portal dosen.
        if (! $student->lecturer_id) {
            return response()->json(['message' => 'Dosen pembimbing belum ditugaskan oleh Kaprodi. Pengajuan magang bisa dikirim setelah dosen pembimbingmu ditetapkan.'], 422);
        }

        $request->validate([
            'company_id'   => 'nullable|exists:companies,id',
            'company_name' => 'required_without:company_id|nullable|string|max:255',
            'pic_name'     => 'required|string|max:255',
            'pic_phone'    => ['nullable', 'string', 'max:50', 'regex:/^[0-9()+\-\s]{7,20}$/'],
            'pic_email'    => 'nullable|email|max:255',
            'position'     => 'nullable|string|max:255',
            'bidang'       => 'nullable|string|max:100',
            'start_date'   => 'required|date',
            'proof_file'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'company_name.required_without' => 'Pilih perusahaan yang ada atau isi nama perusahaan baru.',
            'pic_name.required'             => 'Nama pembimbing industri wajib diisi.',
            'pic_phone.regex'               => 'Nomor HP tidak valid (hanya angka dan simbol + - ( ) spasi).',
            'start_date.required'           => 'Tanggal mulai magang wajib diisi.',
            'proof_file.required'           => 'Bukti penerimaan magang wajib diunggah.',
            'proof_file.mimes'              => 'Bukti harus berformat PDF atau gambar (JPG/PNG).',
            'proof_file.max'                => 'Ukuran file maksimal 10 MB.',
        ]);

        $proofPath = $request->file('proof_file')->store('magang-proofs', 'public');
        $company   = $request->filled('company_id') ? Company::find($request->company_id) : null;

        CompanyRequest::create([
            'student_id'   => $student->id,
            'company_id'   => $company?->id,
            'company_name' => $company ? $company->name : $request->company_name,
            'pic_name'     => $request->pic_name,
            'pic_email'    => $request->pic_email,
            'pic_phone'    => $request->pic_phone,
            'position'     => $request->position,
            'bidang'       => $request->bidang,
            'start_date'   => $request->start_date,
            'proof_file'   => $proofPath,
            'status'       => 'pending',
        ]);

        // Beri tahu Kaprodi ada pengajuan magang baru (sama seperti alur web).
        $companyName = $company ? $company->name : $request->company_name;
        foreach (User::where('role', 'kaprodi')->pluck('id') as $kaprodiId) {
            Notification::kirim(
                $kaprodiId,
                'Pengajuan magang baru dari ' . $request->user()->name,
                'pengajuan_magang',
                'Perusahaan: ' . $companyName . '. Menunggu review Kaprodi.',
                '/kaprodi/pengajuan-magang'
            );
        }

        return response()->json(['message' => 'Pengajuan magang berhasil dikirim. Menunggu review Kaprodi.'], 201);
    }

    /** DELETE /ajukan-magang/{magangRequest} — batalkan pengajuan yang masih pending. */
    public function cancelAjukan(Request $request, CompanyRequest $magangRequest)
    {
        $student = $this->currentStudent($request);
        abort_if($magangRequest->student_id !== $student->id, 403, 'Akses ditolak.');

        if ($magangRequest->status !== 'pending') {
            return response()->json(['message' => 'Hanya pengajuan yang masih menunggu review yang bisa dibatalkan.'], 422);
        }

        if ($magangRequest->proof_file) {
            Storage::disk('public')->delete($magangRequest->proof_file);
        }

        $magangRequest->delete();

        return response()->json(['message' => 'Pengajuan magang dibatalkan. Kamu bisa mengajukan lagi.']);
    }

    /** GET /magang-saya */
    public function magangSaya(Request $request)
    {
        $student    = $this->currentStudent($request);
        $internship = $student->activeInternship()->with(['company', 'lecturer.user', 'lecturerIndustry.user'])->first();

        $checklist        = ($internship && ! $internship->is_finished) ? $this->finishChecklist($student, $internship) : [];
        $canRequestFinish = ! empty($checklist) && ! in_array(false, array_column($checklist, 'met'), true);

        $logBooks = $student->logBooks()->orderByDesc('updated_at')->limit(5)->get()
            ->map(fn ($l) => [
                'id' => $l->id, 'title' => $l->title,
                'date' => optional($l->date)->toDateString(),
                'lecturer_note' => $l->lecturer_note, 'industry_note' => $l->industry_note,
            ]);

        return response()->json([
            'internship' => $internship ? [
                'id'                => $internship->id,
                'company'           => $internship->company->name ?? null,
                'position'          => $internship->position,
                'lecturer'          => $internship->lecturer?->user?->name,
                'lecturer_industry' => $internship->lecturerIndustry?->user?->name,
                'is_finished'       => (bool) $internship->is_finished,
                'finish_requested'  => (bool) $internship->finish_requested,
                'certificate_url'   => $internship->certificate_path ? Storage::url($internship->certificate_path) : null,
            ] : null,
            'finish_checklist'   => $checklist,
            'can_request_finish' => $canRequestFinish,
            'logbooks'           => $logBooks,
        ]);
    }

    /** POST /magang-saya/sertifikat */
    public function uploadCertificate(Request $request)
    {
        $student    = $this->currentStudent($request);
        $internship = $student->activeInternship()->first();

        if (! $internship) {
            return response()->json(['message' => 'Belum ada data magang aktif.'], 422);
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

        return response()->json(['message' => 'Sertifikat magang berhasil diunggah.']);
    }

    /** POST /magang-saya/ajukan-selesai */
    public function requestFinish(Request $request)
    {
        $student    = $this->currentStudent($request);
        $internship = $student->activeInternship()->first();

        if (! $internship || $internship->is_finished) {
            return response()->json(['message' => 'Tidak ada magang aktif yang bisa diajukan selesai.'], 422);
        }
        if ($internship->finish_requested) {
            return response()->json(['message' => 'Pengajuan selesai magang sudah dikirim, tunggu ACC Kaprodi.'], 422);
        }

        $checklist = $this->finishChecklist($student, $internship);
        if (in_array(false, array_column($checklist, 'met'), true)) {
            return response()->json(['message' => 'Belum semua syarat terpenuhi untuk mengajukan selesai magang.'], 422);
        }

        $internship->update(['finish_requested' => true]);

        return response()->json(['message' => 'Pengajuan selesai magang berhasil dikirim. Menunggu ACC Kaprodi.']);
    }

    private function finishChecklist($student, $internship): array
    {
        $report       = $student->report;
        $logbookCount = $student->logBooks()->count();

        $hasLecturerScore = StudentScore::where('internship_id', $internship->id)->where('scorer_type', 'lecturer')->exists();
        $hasIndustryScore = StudentScore::where('internship_id', $internship->id)->where('scorer_type', 'lecturer_industry')->exists();

        return [
            ['label' => 'Sertifikat magang sudah diunggah', 'met' => (bool) $internship->certificate_path, 'hint' => 'Unggah sertifikat magang.'],
            ['label' => 'Laporan akhir sudah di-ACC dosen pembimbing', 'met' => $report && $report->status === 'approved', 'hint' => 'Unggah laporan akhir dan tunggu ACC di menu Laporan Akhir.'],
            ['label' => 'Nilai dari dosen pembimbing (kampus) sudah diisi', 'met' => $hasLecturerScore, 'hint' => 'Nilai belum diinput oleh dosen pembimbing kampus.'],
            ['label' => 'Nilai dari pembimbing industri sudah diisi', 'met' => $hasIndustryScore, 'hint' => 'Nilai belum diinput oleh pembimbing industri.'],
            ['label' => 'Minimal ' . Internship::MIN_LOGBOOK . ' logbook sudah diisi', 'met' => $logbookCount >= Internship::MIN_LOGBOOK, 'hint' => "Baru ada {$logbookCount} logbook, minimal " . Internship::MIN_LOGBOOK . '.'],
        ];
    }
}
