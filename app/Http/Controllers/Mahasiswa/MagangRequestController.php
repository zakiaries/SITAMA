<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MagangRequestController extends Controller
{
    public function index()
    {
        $student  = Auth::user()->student;
        $requests = CompanyRequest::where('student_id', $student->id)->latest()->get();
        $companies = Company::orderBy('name')->get();

        // Pembimbing industri yang SUDAH terdaftar (dari magang sebelumnya) —
        // ditampilkan berlabel perusahaan agar mahasiswa berikutnya tinggal pilih.
        $existingPics = \App\Models\Internship::whereNotNull('lecturer_industry_id')
            ->with(['lecturerIndustry.user', 'company'])
            ->get()
            ->groupBy('lecturer_industry_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'id'      => $first->lecturer_industry_id,
                    'name'    => $first->lecturerIndustry->user->name ?? 'Pembimbing',
                    'company' => $group->pluck('company.name')->filter()->unique()->implode(', '),
                ];
            })->values();

        $hasActiveInternship = $student->internships()->where('is_finished', false)->exists();
        $hasPending          = $requests->where('status', 'pending')->isNotEmpty();
        // Dospem diplot Kaprodi SEBELUM mahasiswa mencari magang (dospem yang
        // membimbing proposal). Tanpa itu, magang yang terbentuk tak punya dosen
        // dan mahasiswa jadi tak terlihat di portal dosen.
        $hasLecturer         = (bool) $student->lecturer_id;

        return view('mahasiswa.ajukan-magang.index', compact(
            'requests', 'companies', 'existingPics', 'hasActiveInternship', 'hasPending', 'hasLecturer'
        ));
    }

    public function store(Request $request)
    {
        $student = Auth::user()->student;

        if ($student->internships()->where('is_finished', false)->exists()) {
            return back()->with('error', 'Kamu sudah memiliki magang aktif.');
        }

        if (CompanyRequest::where('student_id', $student->id)->where('status', 'pending')->exists()) {
            return back()->with('error', 'Kamu sudah memiliki pengajuan yang sedang menunggu review Kaprodi.');
        }

        // Tanpa dosen pembimbing, magang yang disetujui akan lahir tanpa dosen
        // (internships.lecturer_id NULL) dan mahasiswa tak akan pernah muncul di
        // portal dosen. Dospem juga yang membimbing proposal sebelum magang mulai.
        if (! $student->lecturer_id) {
            return back()->with('error',
                'Dosen pembimbing belum ditugaskan oleh Kaprodi. Pengajuan magang bisa dikirim setelah dosen pembimbingmu ditetapkan.');
        }

        $request->validate([
            'company_id'           => 'nullable|exists:companies,id',
            'company_name'         => 'required_without:company_id|nullable|string|max:255',
            'lecturer_industry_id' => 'nullable|exists:lecturers,id',
            'pic_name'             => 'required_without:lecturer_industry_id|nullable|string|max:255',
            'pic_phone'            => ['nullable', 'string', 'max:50', 'regex:/^[0-9()+\-\s]{7,20}$/'],
            'pic_email'            => 'nullable|email|max:255',
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

        $company = $request->filled('company_id')
            ? Company::find($request->company_id)
            : null;

        // PIC yang sudah terdaftar (dipilih dari dropdown) — pastikan role industri.
        $existingPic = $request->filled('lecturer_industry_id')
            ? \App\Models\Lecturer::whereHas('user', fn ($q) => $q->where('role', 'lecturer_industry'))
                ->with('user')->find($request->lecturer_industry_id)
            : null;

        CompanyRequest::create([
            'student_id'           => $student->id,
            'company_id'           => $company?->id,
            'lecturer_industry_id' => $existingPic?->id,
            'company_name'         => $company ? $company->name : $request->company_name,
            'pic_name'             => $existingPic ? $existingPic->user->name : $request->pic_name,
            'pic_email'            => $existingPic ? $existingPic->user->email : $request->pic_email,
            'pic_phone'            => $existingPic ? null : $request->pic_phone,
            'position'             => $request->position,
            'bidang'               => $request->bidang,
            'start_date'           => $request->start_date,
            'proof_file'           => $proofPath,
            'status'               => 'pending',
        ]);

        // Beri tahu Kaprodi ada pengajuan magang baru yang perlu direview.
        $companyName = $company ? $company->name : $request->company_name;
        foreach (User::where('role', 'kaprodi')->pluck('id') as $kaprodiId) {
            Notification::kirim(
                $kaprodiId,
                'Pengajuan magang baru dari ' . Auth::user()->name,
                'pengajuan_magang',
                'Perusahaan: ' . $companyName . '. Menunggu review Kaprodi.',
                '/kaprodi/pengajuan-magang'
            );
        }

        return back()->with('success', 'Pengajuan magang berhasil dikirim. Menunggu review Kaprodi.');
    }

    /**
     * Batalkan pengajuan yang masih menunggu review (belum diproses Kaprodi).
     * Setelah dibatalkan, mahasiswa bisa mengajukan lagi.
     */
    public function cancel(CompanyRequest $magangRequest)
    {
        $student = Auth::user()->student;

        if ($magangRequest->student_id !== $student->id) {
            abort(403);
        }

        if ($magangRequest->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan yang masih menunggu review yang bisa dibatalkan.');
        }

        if ($magangRequest->proof_file) {
            Storage::disk('public')->delete($magangRequest->proof_file);
        }

        $magangRequest->delete();

        return redirect()->route('mahasiswa.ajukan-magang')
            ->with('success', 'Pengajuan magang dibatalkan. Kamu bisa mengajukan lagi.');
    }
}
