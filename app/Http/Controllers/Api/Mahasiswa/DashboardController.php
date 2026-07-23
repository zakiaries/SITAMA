<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use App\Models\SeminarPresenter;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function index(Request $request)
    {
        $student = $this->currentStudent($request);
        $user    = $request->user();

        $student->loadMissing('lecturer.user');
        $internship = $student->activeInternship()->with('company')->first();

        $logBooksCount  = $student->logBooks()->count();
        $guidancesDone  = $student->guidances()->where('status', 'approved')->count();
        $daysInternship = ($internship && $internship->start_date)
            ? now()->diffInDays($internship->start_date)
            : 0;

        $latestGuidances = $student->guidances()->orderByDesc('date')->take(3)->get()
            ->map(fn ($g) => [
                'id'            => $g->id,
                'title'         => $g->title,
                'date'          => optional($g->date)->toDateString(),
                'status'        => $g->status,
                'activity'      => $g->activity,
                'lecturer_note' => $g->lecturer_note,
            ]);

        $latestLogBooks = $student->logBooks()->orderByDesc('date')->take(3)->get()
            ->map(fn ($l) => [
                'id'       => $l->id,
                'title'    => $l->title,
                'date'     => optional($l->date)->toDateString(),
                'activity' => $l->activity,
            ]);

        $notifications = $user->notifications()->where('is_read', false)
            ->orderByDesc('created_at')->take(5)->get()
            ->map(fn ($n) => [
                'id'       => $n->id,
                'message'  => $n->message,
                'category' => $n->category,
                'date'     => $n->date,
            ]);

        // Model sesi-grup: hitung sesi di mana mahasiswa menjadi penyaji.
        $seminarsCount = $student
            ? SeminarPresenter::where('student_id', $student->id)->count()
            : 0;

        return response()->json([
            'user'  => ['name' => $user->name],
            'stats' => [
                'logbook'     => $logBooksCount,
                'bimbingan'   => $guidancesDone,
                'seminar'     => $seminarsCount,
                'hari_magang' => $daysInternship,
            ],
            'internship' => $internship ? [
                'company'     => $internship->company->name ?? null,
                'position'    => $internship->position,
                'lecturer'    => $student->lecturer?->user?->name,
                'start_date'  => optional($internship->start_date)->toDateString(),
                'end_date'    => optional($internship->end_date)->toDateString(),
                'is_finished' => (bool) $internship->is_finished,
            ] : null,
            'latest_guidances' => $latestGuidances,
            'latest_logbooks'  => $latestLogBooks,
            'notifications'    => $notifications,
        ]);
    }
}
