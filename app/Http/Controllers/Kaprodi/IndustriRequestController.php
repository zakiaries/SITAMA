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

class IndustriRequestController extends Controller
{
    public function approve(CompanyRequest $companyRequest)
    {
        if ($companyRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        // Akun perusahaan
        $companyUsername = $this->generateUsername($companyRequest->company_name);
        $companyPassword = Str::random(8);

        $companyUser = User::create([
            'name'     => $companyRequest->company_name,
            'username' => $companyUsername,
            'email'    => $this->uniqueEmail($companyRequest->company_email, $companyUsername),
            'password' => Hash::make($companyPassword),
            'role'     => 'industri',
        ]);

        $company = Company::create([
            'user_id'              => $companyUser->id,
            'name'                 => $companyRequest->company_name,
            'address'              => $companyRequest->company_address,
            'field'                => $companyRequest->company_field,
            'phone'                => $companyRequest->company_phone,
            'email'                => $companyRequest->company_email,
            'verification_status'  => 'verified',
        ]);

        // Akun pembimbing industri
        $picUsername = $this->generateUsername($companyRequest->pic_name);
        $picPassword = Str::random(8);

        $picUser = User::create([
            'name'     => $companyRequest->pic_name,
            'username' => $picUsername,
            'email'    => $this->uniqueEmail($companyRequest->pic_email, $picUsername),
            'password' => Hash::make($picPassword),
            'role'     => 'lecturer_industry',
        ]);

        $lecturer = Lecturer::create(['user_id' => $picUser->id]);

        // Hubungkan ke data magang mahasiswa
        $student = $companyRequest->student;
        $internship = $student->internships()->latest()->first();

        if ($internship) {
            $internship->update([
                'company_id'           => $company->id,
                'lecturer_industry_id' => $lecturer->id,
            ]);
        } else {
            Internship::create([
                'student_id'           => $student->id,
                'lecturer_id'          => $student->lecturer_id,
                'company_id'           => $company->id,
                'lecturer_industry_id' => $lecturer->id,
                'start_date'           => now(),
            ]);
        }

        $companyRequest->update([
            'status'              => 'approved',
            'created_company_id'  => $company->id,
            'created_lecturer_id' => $lecturer->id,
        ]);

        return back()
            ->with('success', "Akun untuk {$companyRequest->company_name} berhasil dibuat dan dihubungkan ke data magang {$student->user->name}.")
            ->with('credentials', [
                'company_name'     => $companyRequest->company_name,
                'company_username' => $companyUsername,
                'company_password' => $companyPassword,
                'pic_name'         => $companyRequest->pic_name,
                'pic_username'     => $picUsername,
                'pic_password'     => $picPassword,
            ]);
    }

    public function reject(Request $request, CompanyRequest $companyRequest)
    {
        if ($companyRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $companyRequest->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', "Pengajuan dari {$companyRequest->student->user->name} telah ditolak.");
    }

    private function generateUsername(string $base): string
    {
        $slug = Str::slug($base, '');
        $slug = $slug !== '' ? substr($slug, 0, 15) : 'user';

        do {
            $username = $slug . rand(100, 999);
        } while (User::where('username', $username)->exists());

        return $username;
    }

    private function uniqueEmail(?string $email, string $usernameFallback): string
    {
        if ($email && !User::where('email', $email)->exists()) {
            return $email;
        }

        return $usernameFallback . '@sitama.local';
    }
}
