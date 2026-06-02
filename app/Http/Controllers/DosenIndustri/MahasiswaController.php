<?php

namespace App\Http\Controllers\DosenIndustri;

use App\Http\Controllers\Controller;
use App\Models\AssessmentComponent;
use App\Models\LogBook;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaController extends Controller
{
    private function getLecturer()
    {
        $lecturer = Auth::user()->lecturer;
        if (!$lecturer) abort(403, 'Akses ditolak.');
        return $lecturer;
    }

    private function getInternship(Student $student, $lecturer)
    {
        $internship = $student->internships()
            ->where('lecturer_industry_id', $lecturer->id)
            ->with('company')
            ->first();

        if (!$internship) abort(404, 'Data magang tidak ditemukan.');
        return $internship;
    }

    public function detail(Request $request, Student $student)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);
        $student->load('user');

        $filter = $request->input('filter', 'semua');

        $query = $student->logBooks()->orderByDesc('date');

        if ($filter === 'belum') {
            $query->whereNull('lecturer_note');
        } elseif ($filter === 'sudah') {
            $query->whereNotNull('lecturer_note');
        }

        $logBooks = $query->get();

        $totalLog     = $student->logBooks()->count();
        $sudahDikomen = $student->logBooks()->whereNotNull('lecturer_note')->count();

        return view('dosen-industri.mahasiswa.detail', compact(
            'student', 'internship', 'logBooks', 'filter', 'totalLog', 'sudahDikomen'
        ));
    }

    public function kirimKomentar(Request $request, Student $student, LogBook $logBook)
    {
        $lecturer = $this->getLecturer();
        $this->getInternship($student, $lecturer);

        $request->validate(['komentar' => 'required|string|max:1000'], [
            'komentar.required' => 'Komentar tidak boleh kosong.',
        ]);

        $logBook->update(['lecturer_note' => $request->komentar]);

        return back()->with('success', 'Komentar berhasil dikirim.');
    }

    public function hapusKomentar(Student $student, LogBook $logBook)
    {
        $lecturer = $this->getLecturer();
        $this->getInternship($student, $lecturer);

        $logBook->update(['lecturer_note' => null]);

        return back()->with('success', 'Komentar berhasil dihapus.');
    }

    public function penilaianPage(Student $student)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);
        $student->load('user');

        $components = AssessmentComponent::with(['detailedComponents' => function ($q) use ($internship) {
            $q->with(['scores' => fn($q2) => $q2->where('internship_id', $internship->id)]);
        }])->get();

        return view('dosen-industri.mahasiswa.penilaian', compact(
            'student', 'internship', 'components'
        ));
    }

    public function simpanPenilaian(Request $request, Student $student)
    {
        $lecturer   = $this->getLecturer();
        $internship = $this->getInternship($student, $lecturer);

        $request->validate([
            'scores'            => 'array',
            'scores.*'          => 'nullable|numeric|min:0|max:100',
            'performance_notes' => 'nullable|string|max:2000',
        ]);

        foreach ((array) $request->scores as $detailId => $score) {
            if ($score !== null && $score !== '') {
                StudentScore::updateOrCreate(
                    [
                        'internship_id'                    => $internship->id,
                        'detailed_assessment_component_id' => $detailId,
                    ],
                    ['score' => $score]
                );
            }
        }

        if ($request->filled('performance_notes')) {
            $internship->update([
                'performance_notes'      => $request->performance_notes,
                'performance_notes_by'   => Auth::user()->name,
                'performance_notes_date' => now()->toDateString(),
            ]);
        }

        return back()->with('success', 'Penilaian berhasil disimpan.');
    }
}
