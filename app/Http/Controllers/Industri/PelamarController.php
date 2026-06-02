<?php

namespace App\Http\Controllers\Industri;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PelamarController extends Controller
{
    private function company(): Company
    {
        $company = Auth::user()->company;
        if (!$company) abort(403, 'Akun ini tidak terhubung dengan data perusahaan.');
        return $company;
    }

    private function authorizeApplication(Company $company, Application $application): void
    {
        $application->loadMissing('jobListing');
        if ($application->jobListing->company_id !== $company->id) abort(403);
    }

    public function index(Request $request)
    {
        $company = $this->company();
        $status  = $request->input('status', 'pending');

        $base = fn() => Application::whereHas('jobListing', fn($q) => $q->where('company_id', $company->id));

        $query = $base()->with(['student.user', 'jobListing']);

        if (in_array($status, ['pending', 'accepted', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student.user', fn($u) => $u->where('name', 'like', "%$search%")
                ->orWhere('username', 'like', "%$search%"));
        }

        $pelamars = $query->latest()->get();

        $counts = [
            'pending'  => $base()->where('status', 'pending')->count(),
            'accepted' => $base()->where('status', 'accepted')->count(),
            'rejected' => $base()->where('status', 'rejected')->count(),
        ];

        return view('industri.pelamar.index', compact('pelamars', 'status', 'counts', 'company'));
    }

    public function accept(Application $application)
    {
        $this->authorizeApplication($this->company(), $application);
        $application->update(['status' => 'accepted']);

        return back()->with('success', 'Pelamar diterima.');
    }

    public function reject(Application $application)
    {
        $this->authorizeApplication($this->company(), $application);
        $application->update(['status' => 'rejected']);

        return back()->with('success', 'Pelamar ditolak.');
    }
}
