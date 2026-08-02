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

        $status = $request->input('status', 'semua');

        // Internship bimbingan dosen ini yang sudah dinilai oleh dosen ini (punya StudentScore scorer_type=lecturer).
        $ownScores = fn($q) => $q->where('scorer_type', 'lecturer');
        $gradedInternship = fn($q) => $q->where('lecturer_id', $lecturer->id)->whereHas('scores', $ownScores);

        $query = Student::dibimbingOleh($lecturer->id)
            ->with([
                'user',
                'internships' => fn($q) => $q->where('lecturer_id', $lecturer->id)
                    ->with('company')->withCount(['scores' => $ownScores])->latest(),
                'guidances',
                'logBooks',
                'report', // dipakai penanda "laporan menunggu review" di kartu
            ]);

        if ($status === 'dinilai') {
            $query->whereHas('internships', $gradedInternship);
        } elseif ($status === 'belum') {
            // Belum dinilai termasuk yang magangnya belum terbentuk sama sekali.
            $query->whereDoesntHave('internships', $gradedInternship);
        }

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

        // Hitungan untuk tab status (mengabaikan filter status, tetap ikut filter dasar bimbingan dosen).
        $base = fn() => Student::dibimbingOleh($lecturer->id);
        $counts = [
            'semua'   => $base()->count(),
            'dinilai' => $base()->whereHas('internships', $gradedInternship)->count(),
            'belum'   => $base()->whereDoesntHave('internships', $gradedInternship)->count(),
        ];

        $majors = Student::dibimbingOleh($lecturer->id)->distinct()->pluck('major');

        $years = Student::dibimbingOleh($lecturer->id)->distinct()->pluck('academic_year');

        return view('dosen.dashboard.index', compact('user', 'lecturer', 'students', 'majors', 'years', 'status', 'counts'));
    }
}
