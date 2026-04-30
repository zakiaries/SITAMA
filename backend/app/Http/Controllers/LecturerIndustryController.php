<?php

namespace App\Http\Controllers;

use App\Models\AssessmentComponent;
use App\Models\LogBook;
use App\Models\StudentScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LecturerIndustryController extends Controller
{
    // ─── Home ────────────────────────────────────────────────────────────────

    public function home(Request $request)
    {
        $user     = $request->user();
        $lecturer = $user->lecturer;

        if (!$lecturer) {
            return response()->json(['errors' => ['message' => 'Data pembimbing tidak ditemukan']], 404);
        }

        $internships = $lecturer->supervisedInternships()
            ->with(['student.user', 'student.logBooks', 'company'])
            ->get();

        $students = $internships->map(fn($i) => $this->formatStudentSummary($i));

        $activeCount  = $internships->filter(fn($i) => !$i->is_finished)->count();
        $newLogbooks  = $internships->sum(fn($i) => $i->student->logBooks()
            ->whereNull('lecturer_note')
            ->where('updated_at', '>=', now()->subDays(7))
            ->count());

        return response()->json([
            'lecturer_name'   => $user->name,
            'company_name'    => $internships->first()?->company->name ?? '-',
            'position'        => 'Pembimbing Industri',
            'division'        => '-',
            'total_students'  => $internships->count(),
            'active_students' => $activeCount,
            'new_logbooks'    => $newLogbooks,
            'students'        => $students,
        ]);
    }

    // ─── Detail Student ──────────────────────────────────────────────────────

    public function detailStudent(Request $request, int $studentId)
    {
        $lecturer = $request->user()->lecturer;

        $internship = $lecturer->supervisedInternships()
            ->with(['student.user', 'student.logBooks'])
            ->where('student_id', $studentId)
            ->firstOrFail();

        $student = $internship->student;
        $user    = $student->user;

        $logbooks = $student->logBooks()->orderBy('date', 'desc')->limit(50)->get()
            ->map(fn($lb) => $this->formatLogbookEntry($lb, $internship));

        $scores    = $this->buildScoreEntity($internship);
        $avgScore  = $scores ? collect($scores['categories'])
            ->flatMap(fn($c) => collect($c['items'])->pluck('score'))
            ->filter()
            ->avg() ?? 0 : 0;

        return response()->json([
            'student_id'             => $student->id,
            'student_name'           => $user->name,
            'nim'                    => $user->username,
            'position'               => $internship->position,
            'status'                 => $this->resolveStatus($internship),
            'start_date'             => $internship->start_date->format('Y-m-d'),
            'end_date'               => $internship->end_date?->format('Y-m-d'),
            'total_logbooks'         => $student->logBooks()->count(),
            'attendance_percentage'  => $this->calcAttendance($internship, $student->logBooks()->count()),
            'performance_notes'      => $internship->performance_notes ?? '',
            'performance_notes_by'   => $internship->performance_notes_by ?? '',
            'performance_notes_date' => $internship->performance_notes_date?->format('Y-m-d'),
            'recent_logbooks'        => $logbooks,
            'assessment_scores'      => $scores ? array_merge($scores, [
                'average_score' => round($avgScore, 1),
                'score_quality' => $this->scoreQuality($avgScore),
            ]) : null,
        ]);
    }

    // ─── Add Logbook Comment ─────────────────────────────────────────────────

    public function addLogbookComment(Request $request, int $logbookId)
    {
        $request->validate(['comment' => 'required|string']);

        $lecturer = $request->user()->lecturer;

        $logbook = LogBook::whereHas('student.internships', function ($q) use ($lecturer) {
            $q->where('lecturer_industry_id', $lecturer->id);
        })->findOrFail($logbookId);

        $logbook->update(['lecturer_note' => $request->comment]);

        return response()->json(['message' => 'Komentar berhasil disimpan']);
    }

    // ─── Submit Assessment ───────────────────────────────────────────────────

    public function submitScores(Request $request, int $studentId)
    {
        $request->validate([
            'scores'       => 'required|array',
            'scores.*.detailed_assessment_components_id' => 'required|integer|exists:detailed_assessment_components,id',
            'scores.*.score' => 'required|numeric|min:0|max:100',
            'performance_notes'    => 'nullable|string',
            'performance_notes_by' => 'nullable|string',
        ]);

        $lecturer   = $request->user()->lecturer;
        $internship = $lecturer->supervisedInternships()
            ->where('student_id', $studentId)
            ->firstOrFail();

        DB::transaction(function () use ($request, $internship) {
            foreach ($request->scores as $item) {
                StudentScore::updateOrCreate(
                    [
                        'internship_id'                    => $internship->id,
                        'detailed_assessment_component_id' => $item['detailed_assessment_components_id'],
                    ],
                    ['score' => $item['score']]
                );
            }

            if ($request->filled('performance_notes')) {
                $internship->update([
                    'performance_notes'      => $request->performance_notes,
                    'performance_notes_by'   => $request->performance_notes_by,
                    'performance_notes_date' => now()->toDateString(),
                ]);
            }
        });

        return response()->json(['message' => 'Penilaian berhasil disimpan']);
    }

    // ─── Profile ─────────────────────────────────────────────────────────────

    public function profile(Request $request)
    {
        $user     = $request->user();
        $lecturer = $user->lecturer;

        $internships    = $lecturer->supervisedInternships()->with('company')->get();
        $activeCount    = $internships->filter(fn($i) => !$i->is_finished)->count();
        $evaluatedCount = $internships->filter(function ($i) {
            return StudentScore::where('internship_id', $i->id)->exists();
        })->count();

        $avgScore = StudentScore::whereIn('internship_id', $internships->pluck('id'))
            ->whereNotNull('score')
            ->avg('score') ?? 0;

        return response()->json([
            'name'               => $user->name,
            'company_name'       => $internships->first()?->company->name ?? '-',
            'position'           => 'Pembimbing Industri',
            'division'           => '-',
            'photo_profile'      => $user->photo_profile ? asset('storage/' . $user->photo_profile) : null,
            'total_students'     => $internships->count(),
            'active_students'    => $activeCount,
            'evaluated_students' => $evaluatedCount,
            'average_score'      => round($avgScore, 1),
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function formatStudentSummary($internship): array
    {
        $student = $internship->student;
        $user    = $student->user;
        $start   = $internship->start_date;
        $end     = $internship->end_date ?? now();
        $total   = max($start->diffInDays($end), 1);
        $elapsed = min($start->diffInDays(now()), $total);
        $progress = round(($elapsed / $total) * 100);

        return [
            'id'                   => $student->id,
            'name'                 => $user->name,
            'nim'                  => $user->username,
            'class_name'           => $student->the_class,
            'position'             => $internship->position,
            'status'               => $this->resolveStatus($internship),
            'progress_percentage'  => $internship->is_finished ? 100.0 : (float) $progress,
            'start_date'           => $start->format('Y-m-d'),
            'end_date'             => $internship->end_date?->format('Y-m-d'),
            'total_logbooks'       => $student->logBooks()->count(),
            'attendance_percentage' => $this->calcAttendance($internship, $student->logBooks()->count()),
        ];
    }

    private function formatLogbookEntry(LogBook $lb, $internship): array
    {
        $dayNumber = $internship->start_date->diffInDays($lb->date) + 1;

        return [
            'id'                  => $lb->id,
            'day_number'          => $dayNumber,
            'title'               => $lb->title,
            'description'         => $lb->activity,
            'category'            => $lb->category ?? 'Umum',
            'date'                => $lb->date->format('Y-m-d'),
            'comment_by_lecturer' => $lb->lecturer_note,
            'has_comment'         => !is_null($lb->lecturer_note),
            'comment_status'      => is_null($lb->lecturer_note) ? 'Belum dikomen' : 'Sudah dikomen',
        ];
    }

    private function buildScoreEntity($internship): ?array
    {
        $components = AssessmentComponent::with('detailedComponents')->get();
        if ($components->isEmpty()) return null;

        $categories = $components->map(function ($component) use ($internship) {
            $items = $component->detailedComponents->map(function ($detail) use ($internship) {
                $score = StudentScore::where('internship_id', $internship->id)
                    ->where('detailed_assessment_component_id', $detail->id)
                    ->value('score');

                return [
                    'item_name' => $detail->name,
                    'score'     => $score ? (int) $score : 0,
                    'max_score' => 100,
                ];
            })->toArray();

            return [
                'category_name' => $component->name,
                'items'         => $items,
            ];
        })->toArray();

        return ['categories' => $categories];
    }

    private function resolveStatus($internship): string
    {
        if ($internship->is_finished) return 'Selesai';
        if ($internship->start_date->diffInDays(now()) <= 7) return 'Baru';
        return 'Aktif';
    }

    private function calcAttendance($internship, int $logbookCount): float
    {
        $workingDays = max($internship->start_date->diffInWeekdays(now()), 1);
        return min(round(($logbookCount / $workingDays) * 100, 1), 100.0);
    }

    private function scoreQuality(float $avg): string
    {
        if ($avg >= 85) return 'Sangat Baik';
        if ($avg >= 70) return 'Baik';
        if ($avg >= 55) return 'Cukup';
        return 'Kurang';
    }
}
