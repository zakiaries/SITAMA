<?php

namespace App\Http\Controllers\DosenIndustri;

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

        $query = Student::whereHas('internships', fn($q) => $q->where('lecturer_industry_id', $lecturer->id))
            ->with([
                'user',
                'internships' => fn($q) => $q->where('lecturer_industry_id', $lecturer->id)
                    ->with('company')->latest(),
                'logBooks',
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%"))
                  ->orWhere('major', 'like', "%$search%");
            });
        }

        $students = $query->get();

        $totalMahasiswa = $students->count();
        $aktif          = $students->filter(fn($s) => !($s->internships->first()?->is_finished ?? true))->count();
        $belumDikomen   = $students->sum(fn($s) => $s->logBooks->whereNull('lecturer_note')->count());

        return view('dosen-industri.dashboard.index', compact(
            'user', 'lecturer', 'students', 'totalMahasiswa', 'aktif', 'belumDikomen'
        ));
    }
}
