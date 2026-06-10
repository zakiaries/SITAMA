<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyRequest;
use Illuminate\Http\Request;

class IndustriController extends Controller
{
    public function index(Request $request)
    {
        $section = $request->input('section', 'verifikasi');
        $status  = $request->input('status', 'pending');
        $search  = $request->input('search');

        $companyCounts = [
            'pending'  => Company::where('verification_status', 'pending')->count(),
            'verified' => Company::where('verification_status', 'verified')->count(),
            'rejected' => Company::where('verification_status', 'rejected')->count(),
        ];

        $requestCounts = [
            'pending'  => CompanyRequest::where('status', 'pending')->count(),
            'approved' => CompanyRequest::where('status', 'approved')->count(),
            'rejected' => CompanyRequest::where('status', 'rejected')->count(),
        ];

        $companies = collect();
        $requests  = collect();

        if ($section === 'permintaan') {
            $query = CompanyRequest::with(['student.user', 'createdCompany.user', 'createdLecturer.user']);

            if (in_array($status, ['pending', 'approved', 'rejected'])) {
                $query->where('status', $status);
            }

            $requests = $query->latest()->get();
        } else {
            $section = 'verifikasi';

            $query = Company::query();

            if (in_array($status, ['pending', 'verified', 'rejected'])) {
                $query->where('verification_status', $status);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('field', 'like', "%$search%");
                });
            }

            $companies = $query->latest()->get();
        }

        return view('kaprodi.industri.index', compact(
            'section', 'status', 'companies', 'companyCounts', 'requests', 'requestCounts'
        ));
    }

    public function verify(Company $company)
    {
        $company->update([
            'verification_status' => 'verified',
            'rejection_reason'    => null,
        ]);

        return back()->with('success', $company->name . ' berhasil diverifikasi.');
    }

    public function reject(Request $request, Company $company)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $company->update([
            'verification_status' => 'rejected',
            'rejection_reason'    => $request->rejection_reason,
        ]);

        return back()->with('success', $company->name . ' telah ditolak.');
    }
}
