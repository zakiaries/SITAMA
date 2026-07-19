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

        $hasActiveInternship = $student->internships()->where('is_finished', false)->exists();
        $hasPending          = $requests->where('status', 'pending')->isNotEmpty();

        return view('mahasiswa.ajukan-magang.index', compact(
            'requests', 'companies', 'hasActiveInternship', 'hasPending'
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

        $request->validate([
            'company_id'   => 'nullable|exists:companies,id',
            'company_name' => 'required_without:company_id|nullable|string|max:255',
            'pic_name'     => 'required|string|max:255',
            'pic_phone'    => 'nullable|string|max:50',
            'pic_email'    => 'nullable|email|max:255',
            'position'     => 'nullable|string|max:255',
            'bidang'       => 'nullable|string|max:100',
            'start_date'   => 'required|date',
            'proof_file'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'company_name.required_without' => 'Pilih perusahaan yang ada atau isi nama perusahaan baru.',
            'pic_name.required'             => 'Nama pembimbing industri wajib diisi.',
            'start_date.required'           => 'Tanggal mulai magang wajib diisi.',
            'proof_file.required'           => 'Bukti penerimaan magang wajib diunggah.',
            'proof_file.mimes'              => 'Bukti harus berformat PDF atau gambar (JPG/PNG).',
            'proof_file.max'                => 'Ukuran file maksimal 10 MB.',
        ]);

        $proofPath = $request->file('proof_file')->store('magang-proofs', 'public');

        $company = $request->filled('company_id')
            ? Company::find($request->company_id)
            : null;

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

        // Beri tahu Kaprodi ada pengajuan magang baru yang perlu direview.
        $companyName = $company ? $company->name : $request->company_name;
        foreach (User::where('role', 'kaprodi')->pluck('id') as $kaprodiId) {
            Notification::create([
                'user_id'     => $kaprodiId,
                'message'     => 'Pengajuan magang baru dari ' . Auth::user()->name,
                'date'        => now()->toDateString(),
                'category'    => 'pengajuan_magang',
                'is_read'     => false,
                'detail_text' => 'Perusahaan: ' . $companyName . '. Menunggu review Kaprodi.',
            ]);
        }

        return back()->with('success', 'Pengajuan magang berhasil dikirim. Menunggu review Kaprodi.');
    }
}
