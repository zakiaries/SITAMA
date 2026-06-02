<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\Seminar;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalMahasiswa = Student::count();
        $belumMagang    = Student::whereDoesntHave('internships')->count();
        $aktif          = Internship::where('is_finished', false)->count();
        $selesai        = Internship::where('is_finished', true)->count();

        $verifIndustri  = Company::where('verification_status', 'pending')->count();
        $totalDosen     = Lecturer::count();
        $totalSeminar   = Seminar::count();

        return view('kaprodi.dashboard.index', compact(
            'user', 'totalMahasiswa', 'belumMagang', 'aktif', 'selesai',
            'verifIndustri', 'totalDosen', 'totalSeminar'
        ));
    }
}
