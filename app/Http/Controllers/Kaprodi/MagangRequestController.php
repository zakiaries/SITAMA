<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyRequest;
use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MagangRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $counts = [
            'pending'  => CompanyRequest::where('status', 'pending')->count(),
            'approved' => CompanyRequest::where('status', 'approved')->count(),
            'rejected' => CompanyRequest::where('status', 'rejected')->count(),
        ];

        $requests = CompanyRequest::with(['student.user', 'company', 'createdLecturer.user'])
            ->where('status', $status)
            ->latest()
            ->get();

        return view('kaprodi.pengajuan-magang.index', compact('requests', 'status', 'counts'));
    }

    public function approve(Request $request, CompanyRequest $magangRequest)
    {
        if ($magangRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $student = $magangRequest->student;

        if ($student->internships()->where('is_finished', false)->exists()) {
            return back()->with('error', 'Mahasiswa ini sudah memiliki magang aktif.');
        }

        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan, pilih yang lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        // Resolve company
        if ($magangRequest->company_id) {
            $companyId = $magangRequest->company_id;
        } else {
            $company   = Company::create([
                'name'                => $magangRequest->company_name,
                'verification_status' => 'verified',
            ]);
            $companyId = $company->id;
        }

        // Buat akun dosen industri dengan kredensial manual
        $picUser = User::create([
            'name'     => $magangRequest->pic_name,
            'username' => $request->username,
            'email'    => $this->uniqueEmail($magangRequest->pic_email, $request->username),
            'password' => Hash::make($request->password),
            'role'     => 'lecturer_industry',
        ]);

        $lecturer = Lecturer::create(['user_id' => $picUser->id]);

        // Catat internship
        Internship::create([
            'student_id'           => $student->id,
            'lecturer_id'          => $student->lecturer_id,
            'company_id'           => $companyId,
            'lecturer_industry_id' => $lecturer->id,
            'position'             => $magangRequest->position,
            'start_date'           => $magangRequest->start_date ?? now(),
            'is_finished'          => false,
        ]);

        $magangRequest->update([
            'status'              => 'approved',
            'created_company_id'  => $companyId,
            'created_lecturer_id' => $lecturer->id,
        ]);

        return back()->with('success', "Pengajuan {$student->user->name} disetujui. Akun pembimbing industri berhasil dibuat dengan username: {$request->username}");
    }

    public function reject(Request $request, CompanyRequest $magangRequest)
    {
        if ($magangRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $magangRequest->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', "Pengajuan {$magangRequest->student->user->name} ditolak.");
    }

    private function uniqueEmail(?string $email, string $fallback): string
    {
        if ($email && !User::where('email', $email)->exists()) {
            return $email;
        }
        return $fallback . '@sitama.local';
    }
}
