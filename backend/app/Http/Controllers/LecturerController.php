<?php

namespace App\Http\Controllers;

use App\Models\AssessmentComponent;
use App\Models\Guidance;
use App\Models\LogBook;
use App\Models\Notification;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LecturerController extends Controller
{
    // ─── Home ────────────────────────────────────────────────────────────────

    public function home(Request $request)
    {
        $user     = $request->user();
        $lecturer = $user->lecturer;

        if (!$lecturer) {
            return response()->json(['errors' => ['message' => 'Data dosen tidak ditemukan']], 404);
        }

        $students = $lecturer->internships()
            ->with(['student.user', 'student.logBooks'])
            ->get()
            ->map(function ($internship) {
                $student = $internship->student;
                $user    = $student->user;

                $latestLogBook = $student->logBooks()->orderBy('updated_at', 'desc')->first();
                $hasNewLogbook = $latestLogBook
                    && $latestLogBook->lecturer_note === null
                    && $latestLogBook->updated_at->diffInDays(now()) <= 7;

                return [
                    'id'           => $student->id,
                    'user_id'      => $user->id,
                    'name'         => $user->name,
                    'username'     => $user->username,
                    'photo_profile' => $user->photo_profile
                        ? asset('storage/' . $user->photo_profile)
                        : null,
                    'class'        => $student->the_class,
                    'study_program' => $student->study_program,
                    'major'        => $student->major,
                    'academic_year' => $student->academic_year,
                    'is_finished'  => $internship->is_finished,
                    'activities'   => [],
                    'hasNewLogbook' => $hasNewLogbook,
                    'lastUpdated'  => $latestLogBook?->updated_at?->toISOString(),
                ];
            });

        return response()->json([
            'name'    => $user->name,
            'userId'  => $user->id,
            'students' => $students,
        ]);
    }

    // ─── Detail Student ──────────────────────────────────────────────────────

    public function detailStudent(Request $request, int $studentId)
    {
        $lecturer = $request->user()->lecturer;

        $internship = $lecturer->internships()
            ->with(['student.user', 'company'])
            ->where('student_id', $studentId)
            ->firstOrFail();

        $student = $internship->student;
        $user    = $student->user;

        $guidances = $student->guidances()->orderBy('date', 'desc')->get()
            ->map(fn($g) => $this->formatGuidance($g));

        $logBooks = $student->logBooks()->orderBy('date', 'desc')->get()
            ->map(fn($lb) => $this->formatLogBook($lb));

        $assessments = $this->buildShowAssessments($internship);
        $avg         = count($assessments) > 0
            ? round(collect($assessments)->avg('average_score'), 2)
            : 0;

        return response()->json([
            'student' => [
                'name'          => $user->name,
                'username'      => $user->username,
                'email'         => $user->email,
                'class'         => $student->the_class,
                'major'         => $student->major,
                'is_finished'   => $internship->is_finished,
                'photo_profile' => $user->photo_profile
                    ? asset('storage/' . $user->photo_profile)
                    : null,
            ],
            'internships' => [[
                'name'       => $internship->company->name,
                'start_date' => $internship->start_date->format('Y-m-d'),
                'end_date'   => $internship->end_date?->format('Y-m-d'),
            ]],
            'guidances'   => $guidances,
            'log_book'    => $logBooks,
            'assessments' => $assessments,
            'average_all_assessments' => $avg,
        ]);
    }

    // ─── Update Guidance Status ───────────────────────────────────────────────

    public function updateGuidanceStatus(Request $request, int $guidanceId)
    {
        $request->validate([
            'status'       => 'required|in:pending,approved,in-progress,rejected',
            'lecturer_note' => 'nullable|string',
        ]);

        $lecturer = $request->user()->lecturer;

        $guidance = Guidance::whereHas('student.internships', function ($q) use ($lecturer) {
            $q->where('lecturer_id', $lecturer->id);
        })->findOrFail($guidanceId);

        $guidance->update([
            'status'       => $request->status,
            'lecturer_note' => $request->lecturer_note,
        ]);

        return response()->json(['data' => $this->formatGuidance($guidance->fresh())]);
    }

    // ─── Update LogBook Note ──────────────────────────────────────────────────

    public function updateLogBookNote(Request $request, int $logBookId)
    {
        $request->validate([
            'lecturer_note' => 'nullable|string',
        ]);

        $lecturer = $request->user()->lecturer;

        $logBook = LogBook::whereHas('student.internships', function ($q) use ($lecturer) {
            $q->where('lecturer_id', $lecturer->id);
        })->findOrFail($logBookId);

        $logBook->update(['lecturer_note' => $request->lecturer_note]);

        return response()->json(['data' => $this->formatLogBook($logBook->fresh())]);
    }

    // ─── Assessments ─────────────────────────────────────────────────────────

    public function getAssessments(Request $request, int $studentId)
    {
        $lecturer = $request->user()->lecturer;

        $internship = $lecturer->internships()
            ->where('student_id', $studentId)
            ->firstOrFail();

        $components = AssessmentComponent::with('detailedComponents')->get();

        $result = $components->map(function ($component) use ($internship) {
            $scores = $component->detailedComponents->map(function ($detail) use ($internship) {
                $score = StudentScore::where('internship_id', $internship->id)
                    ->where('detailed_assessment_component_id', $detail->id)
                    ->first();

                return [
                    'id'    => $detail->id,
                    'name'  => $detail->name,
                    'score' => $score?->score,
                ];
            });

            return [
                'component_name' => $component->name,
                'scores'         => $scores,
            ];
        });

        return response()->json($result);
    }

    public function submitScores(Request $request, int $studentId)
    {
        $request->validate([
            'scores'   => 'required|array',
            'scores.*' => 'array',
            'scores.*.detailed_assessment_components_id' => 'required|integer|exists:detailed_assessment_components,id',
            'scores.*.score' => 'required|numeric|min:0|max:100',
        ]);

        $lecturer = $request->user()->lecturer;

        $internship = $lecturer->internships()
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
        });

        return response()->json(['message' => 'Nilai berhasil disimpan']);
    }

    // ─── Finish Student ───────────────────────────────────────────────────────

    public function finishStudent(Request $request, int $studentId)
    {
        $request->validate([
            'is_finished' => 'required|boolean',
        ]);

        $lecturer = $request->user()->lecturer;

        $internship = $lecturer->internships()
            ->where('student_id', $studentId)
            ->firstOrFail();

        $internship->update(['is_finished' => $request->is_finished]);

        return response()->json(['message' => 'Status mahasiswa berhasil diperbarui']);
    }

    // ─── Add Notification ────────────────────────────────────────────────────

    public function addNotification(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|array',
            'user_id.*'   => 'integer|exists:users,id',
            'message'     => 'required|string',
            'date'        => 'required|date_format:Y-m-d',
            'category'    => 'required|in:guidance,log_book,general,revisi',
            'detail_text' => 'nullable|string',
        ]);

        foreach ($request->user_id as $userId) {
            Notification::create([
                'user_id'     => $userId,
                'message'     => $request->message,
                'date'        => $request->date,
                'category'    => $request->category,
                'detail_text' => $request->detail_text,
                'is_read'     => false,
            ]);
        }

        return response()->json(['message' => 'Notifikasi berhasil dikirim']);
    }

    // ─── Profile ─────────────────────────────────────────────────────────────

    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'name'          => $user->name,
            'username'      => $user->username,
            'photo_profile' => $user->photo_profile
                ? asset('storage/' . $user->photo_profile)
                : null,
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function formatGuidance(Guidance $g): array
    {
        return [
            'id'           => $g->id,
            'title'        => $g->title,
            'activity'     => $g->activity,
            'date'         => $g->date->format('Y-m-d'),
            'lecturer_note' => $g->lecturer_note ?? 'tidak ada catatan',
            'name_file'    => $g->name_file
                ? asset('storage/' . $g->name_file)
                : 'tidak ada file',
            'status'       => $g->status,
        ];
    }

    private function formatLogBook(LogBook $lb): array
    {
        return [
            'id'           => $lb->id,
            'title'        => $lb->title,
            'activity'     => $lb->activity,
            'date'         => $lb->date->format('Y-m-d'),
            'lecturer_note' => $lb->lecturer_note ?? 'tidak ada catatan',
        ];
    }

    private function buildShowAssessments($internship): array
    {
        $components = AssessmentComponent::with('detailedComponents')->get();

        return $components->map(function ($component) use ($internship) {
            $scores = StudentScore::whereIn(
                'detailed_assessment_component_id',
                $component->detailedComponents->pluck('id')
            )
                ->where('internship_id', $internship->id)
                ->whereNotNull('score')
                ->pluck('score');

            return [
                'component_name' => $component->name,
                'average_score'  => $scores->count() > 0
                    ? round($scores->avg(), 2)
                    : 0.0,
            ];
        })->toArray();
    }
}
