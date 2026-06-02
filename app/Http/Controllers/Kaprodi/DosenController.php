<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Lecturer;
use Illuminate\Http\Request;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Lecturer::with('user')
            ->withCount(['internships as students_count' => function ($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw('count(distinct student_id)'));
            }]);

        if ($search) {
            $query->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%")
                ->orWhere('username', 'like', "%$search%"));
        }

        $lecturers = $query->get();

        return view('kaprodi.dosen.index', compact('lecturers'));
    }

    public function detail(Lecturer $lecturer)
    {
        $lecturer->load('user');

        $students = \App\Models\Student::whereHas('internships', fn($q) => $q->where('lecturer_id', $lecturer->id))
            ->with([
                'user',
                'internships' => fn($q) => $q->where('lecturer_id', $lecturer->id)->with('company')->latest(),
            ])
            ->get();

        return view('kaprodi.dosen.detail', compact('lecturer', 'students'));
    }
}
