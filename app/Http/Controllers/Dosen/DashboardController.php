<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $lecturer = $user->lecturer;

        if (!$lecturer) abort(403, 'Akses ditolak.');

        $query = Student::whereHas('internships', fn($q) => $q->where('lecturer_id', $lecturer->id))
            ->with([
                'user',
                'internships' => fn($q) => $q->where('lecturer_id', $lecturer->id)
                    ->with('company')->latest(),
                'guidances',
                'logBooks',
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%"))
                  ->orWhere('major', 'like', "%$search%");
            });
        }

        if ($request->filled('jurusan')) {
            $query->where('major', $request->jurusan);
        }

        if ($request->filled('tahun')) {
            $query->where('academic_year', $request->tahun);
        }

        $students = $query->get();

        $majors = Student::whereHas('internships', fn($q) => $q->where('lecturer_id', $lecturer->id))
            ->distinct()->pluck('major');

        $years = Student::whereHas('internships', fn($q) => $q->where('lecturer_id', $lecturer->id))
            ->distinct()->pluck('academic_year');

        return view('dosen.dashboard.index', compact('user', 'lecturer', 'students', 'majors', 'years'));
    }
}
