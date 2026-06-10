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
        $status = $request->input('status', 'pending');
        $search = $request->input('search');

        $query = Student::with([
            'user',
            'lecturer.user',
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
            case 'pending':
                $query->where('status', 'pending');
                break;
            case 'aktif':
                $query->where('status', 'active')
                      ->whereHas('internships', fn($q) => $q->where('is_finished', false));
                break;
            case 'selesai':
                $query->where('status', 'active')
                      ->whereHas('internships', fn($q) => $q->where('is_finished', true));
                break;
            case 'belum_magang':
                $query->where('status', 'active')
                      ->whereDoesntHave('internships');
                break;
            default:
                $query->where('status', 'active');
        }

        $students = $query->get();

        $counts = [
            'pending'      => Student::where('status', 'pending')->count(),
            'semua'        => Student::where('status', 'active')->count(),
            'aktif'        => Student::where('status', 'active')->whereHas('internships', fn($q) => $q->where('is_finished', false))->count(),
            'selesai'      => Student::where('status', 'active')->whereHas('internships', fn($q) => $q->where('is_finished', true))->count(),
            'belum_magang' => Student::where('status', 'active')->whereDoesntHave('internships')->count(),
        ];

        $lecturers = Lecturer::with('user')->get();

        return view('kaprodi.mahasiswa.index', compact('students', 'status', 'counts', 'lecturers'));
    }

    public function approve(Student $student)
    {
        $student->update(['status' => 'active']);
        return back()->with('success', "Akun {$student->user->name} berhasil disetujui.");
    }

    public function reject(Request $request, Student $student)
    {
        $student->update(['status' => 'rejected']);
        return back()->with('success', "Akun {$student->user->name} telah ditolak.");
    }

    public function detail(Student $student)
    {
        $student->load('user');

        $internship = $student->internships()
            ->with(['company', 'lecturer.user', 'lecturerIndustry.user'])
            ->latest()
            ->first();

        $nilai = $internship?->nilaiSummary();

        return view('kaprodi.mahasiswa.detail', compact('student', 'internship', 'nilai'));
    }

    public function toggleFinished(Student $student)
    {
        $internship = $student->internships()->latest()->first();

        if (!$internship) {
            return back()->with('error', 'Mahasiswa belum memiliki data magang.');
        }

        $internship->update(['is_finished' => !$internship->is_finished]);

        $message = $internship->is_finished
            ? "Magang {$student->user->name} ditandai selesai."
            : "Magang {$student->user->name} ditandai aktif kembali.";

        return back()->with('success', $message);
    }

    public function assignLecturer(Request $request, Student $student)
    {
        $request->validate([
            'lecturer_id' => 'required|exists:lecturers,id',
        ], [
            'lecturer_id.required' => 'Silakan pilih dosen pembimbing.',
        ]);

        // Plot dosen ke student langsung (sebelum magang dimulai)
        $student->update(['lecturer_id' => $request->lecturer_id]);

        // Jika sudah ada internship aktif, sinkronkan juga
        $internship = $student->internships()->latest()->first();
        if ($internship) {
            $internship->update(['lecturer_id' => $request->lecturer_id]);
        }

        return back()->with('success', 'Dosen pembimbing berhasil ditugaskan kepada ' . $student->user->name . '.');
    }
}
