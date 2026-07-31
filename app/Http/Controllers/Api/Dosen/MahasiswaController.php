<?php

namespace App\Http\Controllers\Api\Dosen;

use App\Http\Controllers\Api\ApiController;
use App\Models\AssessmentComponent;
use App\Models\Guidance;
use App\Models\InternshipReport;
use App\Models\LogBook;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MahasiswaController extends ApiController
{
    private function internshipOf(Request $request, Student $student)
    {
        $lecturer   = $this->currentLecturer($request, 'lecturer');
        $internship = $student->internships()->where('lecturer_id', $lecturer->id)->with('company')->first();
        abort_if(! $internship, 404, 'Data magang tidak ditemukan.');
        return $internship;
    }

    public function detail(Request $request, Student $student)
    {
        $internship = $this->internshipOf($request, $student);
        $student->load('user');

        $filter = $request->input('filter', 'semua');
        $period = $request->input('period', 'semua');

        $logQuery = $student->logBooks();
        if ($filter === 'belum') {
            $logQuery->whereNull('lecturer_note');
        } elseif ($filter === 'sudah') {
            $logQuery->whereNotNull('lecturer_note');
        }
        $this->applyPeriod($logQuery, $period);
        if ($filter === 'semua') {
            $logQuery->orderByRaw('lecturer_note IS NOT NULL')->orderByDesc('updated_at');
        } else {
            $logQuery->orderByDesc('updated_at');
        }

        $guidances = $student->guidances()->orderByDesc('updated_at')->get()->map(fn ($g) => [
            'id'            => $g->id,
            'title'         => $g->title,
            'activity'      => $g->activity,
            'date'          => optional($g->date)->toDateString(),
            'status'        => $g->status,
            'lecturer_note' => $g->lecturer_note,
            'file_url'      => $g->name_file ? Storage::url($g->name_file) : null,
        ]);

        $report = $student->report;

        return response()->json([
            'student' => [
                'id'        => $student->id,
                'name'      => $student->user->name,
                'username'  => $student->user->username,
                'major'     => $student->major,
                'the_class' => $student->the_class,
                'photo_url' => $student->user->photoUrl(),
                'email'     => $student->user->email,
            ],
            'internship' => [
                'company'     => $internship->company->name ?? null,
                'position'    => $internship->position,
                'start_date'  => optional($internship->start_date)->toDateString(),
                'end_date'    => optional($internship->end_date)->toDateString(),
                'is_finished' => (bool) $internship->is_finished,
            ],
            'stats' => [
                'bimbingan' => $student->guidances()->count(),
                'logbook'   => $student->logBooks()->count(),
            ],
            'nilai'     => $internship->nilaiSummary(),
            'guidances' => $guidances,
            'report'    => $report ? [
                'id'            => $report->id,
                'title'         => $report->title,
                'status'        => $report->status,
                'lecturer_note' => $report->lecturer_note,
                'file_url'      => $report->file_path ? Storage::url($report->file_path) : null,
            ] : null,
            'logbooks'  => $logQuery->get()->map(fn ($l) => [
                'id'            => $l->id,
                'title'         => $l->title,
                'activity'      => $l->activity,
                'date'          => optional($l->date)->toDateString(),
                'updated_at'    => optional($l->updated_at)->toDateTimeString(),
                'lecturer_note' => $l->lecturer_note,
            ]),
            'filter' => $filter,
            'period' => $period,
        ]);
    }

    public function approveBimbingan(Request $request, Student $student, Guidance $guidance)
    {
        $this->internshipOf($request, $student);
        abort_unless($guidance->student_id === $student->id, 404);
        $guidance->update(['status' => 'approved', 'lecturer_note' => $request->input('note')]);
        return response()->json(['message' => 'Bimbingan berhasil disetujui.']);
    }

    public function revisiBimbingan(Request $request, Student $student, Guidance $guidance)
    {
        $this->internshipOf($request, $student);
        abort_unless($guidance->student_id === $student->id, 404);
        $request->validate(['note' => 'required|string'], ['note.required' => 'Catatan revisi wajib diisi.']);
        $guidance->update(['status' => 'rejected', 'lecturer_note' => $request->note]);
        return response()->json(['message' => 'Bimbingan ditandai untuk revisi.']);
    }

    public function approveLaporan(Request $request, Student $student, InternshipReport $report)
    {
        $this->internshipOf($request, $student);
        abort_if($report->student_id !== $student->id, 404);
        $report->update(['status' => 'approved', 'lecturer_note' => $request->input('note'), 'reviewed_at' => now()]);
        return response()->json(['message' => 'Laporan akhir berhasil disetujui.']);
    }

    public function revisiLaporan(Request $request, Student $student, InternshipReport $report)
    {
        $this->internshipOf($request, $student);
        abort_if($report->student_id !== $student->id, 404);
        $request->validate(['note' => 'required|string'], ['note.required' => 'Catatan revisi wajib diisi.']);
        $report->update(['status' => 'rejected', 'lecturer_note' => $request->note, 'reviewed_at' => now()]);
        return response()->json(['message' => 'Laporan ditandai untuk revisi.']);
    }

    public function logBookNote(Request $request, Student $student, LogBook $logBook)
    {
        $this->internshipOf($request, $student);
        abort_unless($logBook->student_id === $student->id, 404);
        $request->validate(['note' => 'nullable|string']);
        $logBook->update(['lecturer_note' => $request->input('note')]);
        return response()->json(['message' => 'Catatan logbook berhasil disimpan.']);
    }

    public function nilaiPage(Request $request, Student $student)
    {
        $internship = $this->internshipOf($request, $student);
        $student->load('user');

        $components = AssessmentComponent::forScorer('lecturer')->with(['detailedComponents' => function ($q) use ($internship) {
            $q->with(['scores' => fn ($q2) => $q2->where('internship_id', $internship->id)->where('scorer_type', 'lecturer')]);
        }])->get()->map(fn ($c) => [
            'id'      => $c->id,
            'name'    => $c->name,
            'weight'  => $c->weight !== null ? (float) $c->weight : null,
            'details' => $c->detailedComponents->map(fn ($d) => [
                'id'    => $d->id,
                'name'  => $d->name,
                'score' => $d->scores->first()?->score,
            ]),
        ]);

        return response()->json([
            'student'    => ['name' => $student->user->name, 'username' => $student->user->username],
            'components' => $components,
        ]);
    }

    public function updateNilai(Request $request, Student $student)
    {
        $internship = $this->internshipOf($request, $student);

        $request->validate([
            'scores'   => 'required|array',
            'scores.*' => 'nullable|numeric|min:1|max:10',
        ]);

        foreach ($request->scores as $detailId => $score) {
            if ($score !== null && $score !== '') {
                StudentScore::updateOrCreate(
                    ['internship_id' => $internship->id, 'detailed_assessment_component_id' => $detailId, 'scorer_type' => 'lecturer'],
                    ['score' => $score]
                );
            }
        }

        return response()->json(['message' => 'Nilai berhasil diperbarui.']);
    }

    private function applyPeriod($query, string $period): void
    {
        if ($period === '7hari') {
            $query->where('date', '>=', now()->subDays(7));
        } elseif ($period === '30hari') {
            $query->where('date', '>=', now()->subDays(30));
        } elseif ($period === 'bulan_ini') {
            $query->whereMonth('date', now()->month)->whereYear('date', now()->year);
        }
    }
}
