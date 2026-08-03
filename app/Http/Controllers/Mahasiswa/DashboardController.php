<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $student = $user->student;

        $internship     = null;
        $logBooksCount  = 0;
        $guidancesDone  = 0;
        $daysInternship = 0;
        $latestGuidances = collect();
        $latestLogBooks  = collect();

        if ($student) {
            $student->loadMissing('lecturer.user');
            $internship = $student->activeInternship()->with('company')->first();

            $logBooksCount = $student->logBooks()->count();

            $guidancesDone = $student->guidances()
                ->where('status', 'approved')->count();

            if ($internship && $internship->start_date) {
                $daysInternship = now()->diffInDays($internship->start_date);
            }

            $latestGuidances = $student->guidances()
                ->orderByDesc('updated_at')->take(3)->get();

            $latestLogBooks = $student->logBooks()
                ->orderByDesc('updated_at')->take(3)->get();
        }

        // Notifikasi tak lagi diambil di sini: isinya kini ditampilkan panel
        // melayang di lonceng (komponen x-notif-bell), yang menyiapkan datanya
        // sendiri agar tersedia di seluruh halaman, bukan cuma di dashboard.

        // Seminar umum + seminar yang diajukan mahasiswa ini (bukan punya mahasiswa lain).
        $seminarsCount = \App\Models\Seminar::whereNull('student_id')
            ->when($student, fn($q) => $q->orWhere('student_id', $student->id))
            ->count();

        return view('mahasiswa.dashboard.index', compact(
            'user', 'student', 'internship',
            'logBooksCount', 'guidancesDone', 'daysInternship', 'seminarsCount',
            'latestGuidances', 'latestLogBooks'
        ));
    }
}
