<?php

namespace App\Http\Controllers;

use App\Models\Seminar;
use App\Models\SeminarRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeminarController extends Controller
{
    // ─── Student: list & register ────────────────────────────────────────────

    public function index(Request $request)
    {
        $student  = $request->user()->student;
        $seminars = Seminar::whereNull('student_id') // general seminars only
            ->where('status', 'scheduled')
            ->orderBy('date')
            ->get()
            ->map(fn($s) => $this->formatSeminar($s, $student->id));

        return response()->json(['seminars' => $seminars]);
    }

    public function register(Request $request, int $seminarId)
    {
        $student = $request->user()->student;

        SeminarRegistration::firstOrCreate([
            'seminar_id' => $seminarId,
            'student_id' => $student->id,
        ]);

        return response()->json(['message' => 'Berhasil mendaftar seminar']);
    }

    // ─── Kaprodi: manage seminars ─────────────────────────────────────────────

    public function kaprodiIndex(Request $request)
    {
        $status = $request->query('status', 'scheduled');
        $search = $request->query('search', '');

        $query = Seminar::with('student.user')->whereNotNull('student_id');

        if ($status === 'belum_jadwal') {
            $query->whereNull('date');
        } elseif ($status === 'scheduled') {
            $query->whereNotNull('date')->where('status', 'scheduled');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        }

        if ($search) {
            $query->whereHas('student.user', fn($q) =>
                $q->where('name', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%"));
        }

        $seminars = $query->get()->map(fn($s) => $this->formatKaprodiSeminar($s));

        return response()->json(['seminars' => $seminars]);
    }

    public function updateSchedule(Request $request, int $seminarId)
    {
        $request->validate([
            'date'     => 'nullable|date_format:Y-m-d',
            'time'     => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $seminar = Seminar::findOrFail($seminarId);
        $seminar->update($request->only(['date', 'time', 'location']));

        return response()->json(['message' => 'Jadwal berhasil diperbarui']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'program'     => 'required|string',
            'date'        => 'nullable|date_format:Y-m-d',
            'time'        => 'nullable|string',
            'location'    => 'nullable|string',
            'organizer'   => 'nullable|string',
            'description' => 'nullable|string',
            'student_id'  => 'nullable|exists:students,id',
        ]);

        $seminar = Seminar::create(array_merge(
            $request->only(['title', 'program', 'date', 'time', 'location', 'organizer', 'description', 'student_id']),
            ['qr_code' => Str::uuid()]
        ));

        return response()->json(['data' => $this->formatSeminar($seminar, null)], 201);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function formatSeminar(Seminar $s, ?int $studentId): array
    {
        $isRegistered = $studentId
            ? SeminarRegistration::where('seminar_id', $s->id)->where('student_id', $studentId)->exists()
            : false;

        return [
            'id'          => $s->id,
            'title'       => $s->title,
            'program'     => $s->program,
            'date'        => $s->date?->format('Y-m-d'),
            'time'        => $s->time ?? '-',
            'location'    => $s->location ?? '-',
            'organizer'   => $s->organizer ?? '-',
            'description' => $s->description ?? '',
            'qr_code'     => $s->qr_code ?? '',
            'status'      => $isRegistered ? 'registered' : $s->status,
        ];
    }

    private function formatKaprodiSeminar(Seminar $s): array
    {
        return [
            'id'          => $s->id,
            'title'       => $s->title,
            'program'     => $s->program,
            'date'        => $s->date?->format('Y-m-d'),
            'time'        => $s->time,
            'location'    => $s->location,
            'organizer'   => $s->organizer,
            'description' => $s->description,
            'status'      => $s->date ? $s->status : 'belum_jadwal',
            'student' => $s->student ? [
                'id'    => $s->student->id,
                'name'  => $s->student->user->name,
                'nim'   => $s->student->user->username,
                'class' => $s->student->the_class,
            ] : null,
        ];
    }
}
