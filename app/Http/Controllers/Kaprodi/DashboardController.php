<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
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

        $totalMahasiswa  = Student::count();
        $pendingMahasiswa = Student::where('status', 'pending')->count();
        $belumMagang     = Student::where('status', 'active')->whereDoesntHave('internships')->count();
        $aktif           = Internship::where('is_finished', false)->count();
        $selesai         = Internship::where('is_finished', true)->count();

        $totalDosen   = Lecturer::count();
        $totalSeminar = Seminar::count();

        $pendingList = Student::with('user')->where('status', 'pending')->latest()->take(3)->get();

        return view('kaprodi.dashboard.index', compact(
            'user', 'totalMahasiswa', 'pendingMahasiswa', 'belumMagang', 'aktif', 'selesai',
            'totalDosen', 'totalSeminar', 'pendingList'
        ));
    }
}
