<?php

namespace App\Http\Controllers\Api\DosenIndustri;

use App\Http\Controllers\Api\ApiController;
use App\Models\AssessmentComponent;
use App\Models\LogBook;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Http\Request;

class MahasiswaController extends ApiController
{
    private function internshipOf(Request $request, Student $student)
    {
        $lecturer   = $this->currentLecturer($request, 'lecturer_industry');
        $internship = $student->internships()->where('lecturer_industry_id', $lecturer->id)->with('company')->first();
        abort_if(! $internship, 404, 'Data magang tidak ditemukan.');
        return $internship;
    }

    public function detail(Request $request, Student $student)
    {
        $internship = $this->internshipOf($request, $student);
        $student->load('user');

        $filter = $request->input('filter', 'semua');
        $period = $request->input('period', 'semua');

        $query = $student->logBooks();
        if ($filter === 'belum') {
            $query->whereNull('industry_note');
        } elseif ($filter === 'sudah') {
            $query->whereNotNull('industry_note');
        }
        if ($period === '7hari') {
            $query->where('date', '>=', now()->subDays(7));
        } elseif ($period === '30hari') {
            $query->where('date', '>=', now()->subDays(30));
        } elseif ($period === 'bulan_ini') {
            $query->whereMonth('date', now()->month)->whereYear('date', now()->year);
        }
        if ($filter === 'semua') {
            $query->orderByRaw('industry_note IS NOT NULL')->orderByDesc('updated_at');
        } else {
            $query->orderByDesc('updated_at');
        }

        return response()->json([
            'student' => [
                'id'        => $student->id,
                'name'      => $student->user->name,
                'username'  => $student->user->username,
                'the_class' => $student->the_class,
            ],
            'internship' => [
                'company'     => $internship->company->name ?? null,
                'position'    => $internship->position,
                'start_date'  => optional($internship->start_date)->toDateString(),
                'end_date'    => optional($internship->end_date)->toDateString(),
                'is_finished' => (bool) $internship->is_finished,
            ],
            'stats' => [
                'logbook_total'   => $student->logBooks()->count(),
                'logbook_dikomen' => $student->logBooks()->whereNotNull('industry_note')->count(),
            ],
            'logbooks' => $query->get()->map(fn ($l) => [
                'id'            => $l->id,
                'title'         => $l->title,
                'activity'      => $l->activity,
                'date'          => optional($l->date)->toDateString(),
                'updated_at'    => optional($l->updated_at)->toDateTimeString(),
                'industry_note' => $l->industry_note,
            ]),
            'filter' => $filter,
            'period' => $period,
        ]);
    }

    public function kirimKomentar(Request $request, Student $student, LogBook $logBook)
    {
        $this->internshipOf($request, $student);
        abort_unless($logBook->student_id === $student->id, 404);
        $request->validate(['komentar' => 'required|string|max:1000'], ['komentar.required' => 'Komentar tidak boleh kosong.']);
        $logBook->update(['industry_note' => $request->komentar]);
        return response()->json(['message' => 'Komentar berhasil dikirim.']);
    }

    public function hapusKomentar(Request $request, Student $student, LogBook $logBook)
    {
        $this->internshipOf($request, $student);
        abort_unless($logBook->student_id === $student->id, 404);
        $logBook->update(['industry_note' => null]);
        return response()->json(['message' => 'Komentar berhasil dihapus.']);
    }

    public function penilaianPage(Request $request, Student $student)
    {
        $internship = $this->internshipOf($request, $student);
        $student->load('user');

        $components = AssessmentComponent::forScorer('lecturer_industry')->with(['detailedComponents' => function ($q) use ($internship) {
            $q->with(['scores' => fn ($q2) => $q2->where('internship_id', $internship->id)->where('scorer_type', 'lecturer_industry')]);
        }])->get()->map(fn ($c) => [
            'id'      => $c->id,
            'name'    => $c->name,
            'details' => $c->detailedComponents->map(fn ($d) => [
                'id'    => $d->id,
                'name'  => $d->name,
                'score' => $d->scores->first()?->score,
            ]),
        ]);

        return response()->json([
            'student'           => ['name' => $student->user->name, 'username' => $student->user->username],
            'performance_notes' => $internship->performance_notes,
            'components'        => $components,
        ]);
    }

    public function simpanPenilaian(Request $request, Student $student)
    {
        $internship = $this->internshipOf($request, $student);

        $request->validate([
            'scores'            => 'array',
            'scores.*'          => 'nullable|numeric|min:1|max:10',
            'performance_notes' => 'nullable|string|max:2000',
        ]);

        foreach ((array) $request->scores as $detailId => $score) {
            if ($score !== null && $score !== '') {
                StudentScore::updateOrCreate(
                    ['internship_id' => $internship->id, 'detailed_assessment_component_id' => $detailId, 'scorer_type' => 'lecturer_industry'],
                    ['score' => $score]
                );
            }
        }

        if ($request->filled('performance_notes')) {
            $internship->update([
                'performance_notes'      => $request->performance_notes,
                'performance_notes_by'   => $request->user()->name,
                'performance_notes_date' => now()->toDateString(),
            ]);
        }

        return response()->json(['message' => 'Penilaian berhasil disimpan.']);
    }
}
