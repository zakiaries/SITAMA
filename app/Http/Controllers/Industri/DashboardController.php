<?php

namespace App\Http\Controllers\Industri;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $company = $user->company;

        if (!$company) abort(403, 'Akun ini tidak terhubung dengan data perusahaan.');

        $lowonganAktif = $company->jobListings()->where('status', 'active')->count();
        $totalLowongan = $company->jobListings()->count();
        $magangAktif   = $company->internships()->where('is_finished', false)->count();

        $pelamarPending = Application::whereHas('jobListing', fn($q) => $q->where('company_id', $company->id))
            ->where('status', 'pending')->count();
        $pelamarDiterima = Application::whereHas('jobListing', fn($q) => $q->where('company_id', $company->id))
            ->where('status', 'accepted')->count();

        return view('industri.dashboard.index', compact(
            'user', 'company', 'lowonganAktif', 'totalLowongan',
            'magangAktif', 'pelamarPending', 'pelamarDiterima'
        ));
    }
}
