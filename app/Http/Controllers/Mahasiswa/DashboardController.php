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
        $notifications   = collect();

        if ($student) {
            $internship = $student->activeInternship()->with('company')->first();

            $logBooksCount = $student->logBooks()->count();

            $guidancesDone = $student->guidances()
                ->where('status', 'approved')->count();

            if ($internship && $internship->start_date) {
                $daysInternship = now()->diffInDays($internship->start_date);
            }

            $latestGuidances = $student->guidances()
                ->orderByDesc('date')->take(3)->get();

            $latestLogBooks = $student->logBooks()
                ->orderByDesc('date')->take(3)->get();
        }

        $notifications = $user->notifications()
            ->where('is_read', false)->orderByDesc('created_at')->take(5)->get();

        $seminarsCount = \App\Models\Seminar::count();

        return view('mahasiswa.dashboard.index', compact(
            'user', 'student', 'internship',
            'logBooksCount', 'guidancesDone', 'daysInternship', 'seminarsCount',
            'latestGuidances', 'latestLogBooks', 'notifications'
        ));
    }
}
