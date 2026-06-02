<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'semua');
        $search = $request->input('search');

        $query = Student::with([
            'user',
            'internships' => fn($q) => $q->with(['company', 'lecturer.user'])->latest(),
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%"))
                  ->orWhere('major', 'like', "%$search%");
            });
        }

        switch ($status) {
            case 'aktif':
                $query->whereHas('internships', fn($q) => $q->where('is_finished', false));
                break;
            case 'selesai':
                $query->whereHas('internships', fn($q) => $q->where('is_finished', true));
                break;
            case 'belum_magang':
                $query->whereDoesntHave('internships');
                break;
        }

        $students = $query->get();

        // Hitungan untuk badge tab
        $counts = [
            'semua'        => Student::count(),
            'aktif'        => Student::whereHas('internships', fn($q) => $q->where('is_finished', false))->count(),
            'selesai'      => Student::whereHas('internships', fn($q) => $q->where('is_finished', true))->count(),
            'belum_magang' => Student::whereDoesntHave('internships')->count(),
        ];

        $lecturers = Lecturer::with('user')->get();

        return view('kaprodi.mahasiswa.index', compact('students', 'status', 'counts', 'lecturers'));
    }

    public function assignLecturer(Request $request, Student $student)
    {
        $request->validate([
            'lecturer_id' => 'required|exists:lecturers,id',
        ], [
            'lecturer_id.required' => 'Silakan pilih dosen pembimbing.',
        ]);

        $internship = $student->internships()->latest()->first();

        if (!$internship) {
            return back()->with('error', 'Mahasiswa belum memiliki data magang, tidak dapat menugaskan dosen.');
        }

        $internship->update(['lecturer_id' => $request->lecturer_id]);

        return back()->with('success', 'Dosen pembimbing berhasil ditugaskan.');
    }
}
