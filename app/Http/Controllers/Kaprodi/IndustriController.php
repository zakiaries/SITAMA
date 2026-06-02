<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class IndustriController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $search = $request->input('search');

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

        $counts = [
            'pending'  => Company::where('verification_status', 'pending')->count(),
            'verified' => Company::where('verification_status', 'verified')->count(),
            'rejected' => Company::where('verification_status', 'rejected')->count(),
        ];

        return view('kaprodi.industri.index', compact('companies', 'status', 'counts'));
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
