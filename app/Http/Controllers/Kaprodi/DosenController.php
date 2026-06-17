<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tab    = $request->input('tab', 'dosen');

        $role = $tab === 'industri' ? 'lecturer_industry' : 'lecturer';

        $query = Lecturer::with('user')
            ->whereHas('user', fn($u) => $u->where('role', $role))
            ->withCount(['internships as students_count' => function ($q) use ($role) {
                $col = $role === 'lecturer_industry' ? 'lecturer_industry_id' : 'lecturer_id';
                $q->select(\Illuminate\Support\Facades\DB::raw('count(distinct student_id)'))
                  ->whereColumn($col, 'lecturers.id');
            }]);

        if ($search) {
            $query->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%")
                ->orWhere('username', 'like', "%$search%"));
        }

        $lecturers = $query->get();

        $counts = [
            'dosen'   => Lecturer::whereHas('user', fn($u) => $u->where('role', 'lecturer'))->count(),
            'industri'=> Lecturer::whereHas('user', fn($u) => $u->where('role', 'lecturer_industry'))->count(),
        ];

        return view('kaprodi.dosen.index', compact('lecturers', 'tab', 'counts'));
    }

    public function detail(Lecturer $lecturer)
    {
        $lecturer->load('user');

        $isIndustry = $lecturer->user->role === 'lecturer_industry';

        if ($isIndustry) {
            $students = \App\Models\Student::whereHas('internships', fn($q) => $q->where('lecturer_industry_id', $lecturer->id))
                ->with([
                    'user',
                    'internships' => fn($q) => $q->where('lecturer_industry_id', $lecturer->id)->with('company')->latest(),
                ])
                ->get();
        } else {
            $students = \App\Models\Student::whereHas('internships', fn($q) => $q->where('lecturer_id', $lecturer->id))
                ->with([
                    'user',
                    'internships' => fn($q) => $q->where('lecturer_id', $lecturer->id)->with('company')->latest(),
                ])
                ->get();
        }

        return view('kaprodi.dosen.detail', compact('lecturer', 'students', 'isIndustry'));
    }

    public function resetPassword(Request $request, Lecturer $lecturer)
    {
        $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $lecturer->user->update(['password' => Hash::make($request->new_password)]);

        return back()->with('success', "Password {$lecturer->user->name} berhasil direset.");
    }
}
