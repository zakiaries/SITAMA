<?php

namespace App\Http\Controllers\Industri;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $company = $user->company;

        if (!$company) abort(403, 'Akun ini tidak terhubung dengan data perusahaan.');

        $internships = $company->internships()
            ->with(['student.user', 'lecturerIndustry.user'])
            ->latest()
            ->get();

        $magangAktif   = $internships->where('is_finished', false)->count();
        $magangSelesai = $internships->where('is_finished', true)->count();

        return view('industri.dashboard.index', compact(
            'user', 'company', 'internships', 'magangAktif', 'magangSelesai'
        ));
    }
}
