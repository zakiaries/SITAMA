<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use Illuminate\Http\Request;

class SeminarController extends ApiController
{
    public function index(Request $request)
    {
        $student = $this->currentStudent($request);

        $seminars = Seminar::whereNull('student_id')->with('registrations')
            ->orderByDesc('date')->get();

        $mySeminars = Seminar::where('student_id', $student->id)->with('registrations', 'attendances')
            ->orderByDesc('date')->get();

        $peerSeminars = Seminar::whereNotNull('student_id')
            ->where('student_id', '!=', $student->id)
            ->where('status', 'scheduled')
            ->with(['registrations', 'student.user'])
            ->orderByDesc('date')->get();

        $registeredIds = SeminarRegistration::where('student_id', $student->id)
            ->pluck('seminar_id')->toArray();

        $requirements = $this->requirements($student);
        $canSubmit    = ! in_array(false, array_column($requirements, 'met'), true);

        return response()->json([
            'can_submit'     => $canSubmit,
            'requirements'   => $requirements,
            'my_seminars'    => $mySeminars->map(fn ($s) => $this->ownerCard($s, $registeredIds)),
            'peer_seminars'  => $peerSeminars->map(fn ($s) => $this->card($s, $registeredIds)),
            'seminars'       => $seminars->map(fn ($s) => $this->card($s, $registeredIds)),
        ]);
    }

    public function show(Request $request, Seminar $seminar)
    {
        $student = $this->currentStudent($request);
        $seminar->load(['registrations.student.user']);

        $isRegistered = SeminarRegistration::where('student_id', $student->id)
            ->where('seminar_id', $seminar->id)->exists();

        return response()->json([
            'seminar' => [
                'id'          => $seminar->id,
                'title'       => $seminar->title,
                'program'     => $seminar->program,
                'date'        => optional($seminar->date)->toDateString(),
                'time'        => $seminar->time,
                'location'    => $seminar->location,
                'organizer'   => $seminar->organizer,
                'description' => $seminar->description,
                'status'      => $seminar->status,
                'audience'    => $seminar->registrations->count(),
                'min_audience'=> Seminar::MIN_AUDIENCE,
            ],
            'is_registered' => $isRegistered,
            'audiences'     => $seminar->registrations->map(fn ($r) => $r->student->user->name ?? '-'),
        ]);
    }

    public function store(Request $request)
    {
        $student      = $this->currentStudent($request);
        $requirements = $this->requirements($student);

        if (in_array(false, array_column($requirements, 'met'), true)) {
            return response()->json(['message' => 'Anda belum memenuhi semua syarat untuk mengajukan jadwal seminar.'], 422);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'date'        => 'required|date|after_or_equal:today',
            'time'        => 'nullable|string|max:50',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ], ['date.after_or_equal' => 'Tanggal seminar tidak boleh sebelum hari ini.']);

        // Sama seperti web: diajukan sebagai 'pending', menunggu ACC Kaprodi.
        // access_token & QR absensi dibuat otomatis oleh Kaprodi saat menyetujui.
        $seminar = Seminar::create([
            'title'       => $request->title,
            'program'     => $student->study_program ?: 'Magang',
            'date'        => $request->date,
            'time'        => $request->time,
            'location'    => $request->location,
            'organizer'   => $request->user()->name,
            'description' => $request->description,
            'status'      => 'pending',
            'student_id'  => $student->id,
        ]);

        return response()->json(['message' => 'Jadwal seminar berhasil diajukan. Menunggu persetujuan Kaprodi.', 'id' => $seminar->id], 201);
    }

    public function update(Request $request, Seminar $seminar)
    {
        $this->authorizeOwn($request, $seminar);

        if (! in_array($seminar->status, ['pending', 'rejected'], true)) {
            return response()->json(['message' => 'Seminar yang sudah disetujui tidak dapat diubah.'], 422);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'date'        => 'required|date|after_or_equal:today',
            'time'        => 'nullable|string|max:50',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ], ['date.after_or_equal' => 'Tanggal seminar tidak boleh sebelum hari ini.']);

        // Mengajukan ulang → kembali menunggu & bersihkan alasan penolakan (seperti web).
        $seminar->update([
            'title'            => $request->title,
            'date'             => $request->date,
            'time'             => $request->time,
            'location'         => $request->location,
            'description'      => $request->description,
            'status'           => 'pending',
            'rejection_reason' => null,
        ]);

        return response()->json(['message' => 'Jadwal seminar berhasil diperbarui. Menunggu persetujuan Kaprodi.']);
    }

    public function destroy(Request $request, Seminar $seminar)
    {
        $this->authorizeOwn($request, $seminar);

        if (! in_array($seminar->status, ['pending', 'rejected'], true)) {
            return response()->json(['message' => 'Seminar yang sudah disetujui tidak dapat dibatalkan.'], 422);
        }

        $seminar->registrations()->delete();
        $seminar->attendances()->delete();
        $seminar->delete();

        return response()->json(['message' => 'Pengajuan seminar dibatalkan.']);
    }

    public function register(Request $request, Seminar $seminar)
    {
        $student = $this->currentStudent($request);

        SeminarRegistration::firstOrCreate(
            ['student_id' => $student->id, 'seminar_id' => $seminar->id],
            ['status' => 'registered']
        );

        return response()->json(['message' => 'Berhasil mendaftar seminar!']);
    }

    private function authorizeOwn(Request $request, Seminar $seminar): void
    {
        $student = $this->currentStudent($request);
        abort_if($seminar->student_id !== $student->id, 403, 'Akses ditolak.');
    }

    private function requirements($student): array
    {
        $internship = $student->activeInternship()->first();

        return [[
            'key'   => 'is_finished',
            'label' => 'Magang sudah ditandai selesai oleh Kaprodi',
            'met'   => (bool) ($internship?->is_finished),
            'hint'  => 'Ajukan selesai magang di halaman Magang Saya dan tunggu ACC Kaprodi.',
        ]];
    }

    private function card(Seminar $s, array $registeredIds): array
    {
        return [
            'id'           => $s->id,
            'title'        => $s->title,
            'program'      => $s->program,
            'date'         => optional($s->date)->toDateString(),
            'time'         => $s->time,
            'location'     => $s->location,
            'status'       => $s->status,
            'audience'     => $s->registrations->count(),
            'min_audience' => Seminar::MIN_AUDIENCE,
            'is_registered'=> in_array($s->id, $registeredIds, true),
        ];
    }

    /** Kartu untuk seminar milik sendiri: tambahan info QR absensi tamu + alasan tolak. */
    private function ownerCard(Seminar $s, array $registeredIds): array
    {
        return array_merge($this->card($s, $registeredIds), [
            'is_owner'         => true,
            'rejection_reason' => $s->rejection_reason,
            'guest_count'      => $s->attendances->count(),
            'min_guests'       => Seminar::MIN_GUESTS,
            // QR absensi tamu hanya aktif untuk seminar yang sudah di-ACC Kaprodi (punya token).
            'hadir_url'        => ($s->status === 'scheduled' && $s->access_token)
                ? url('/seminar/hadir/' . $s->access_token)
                : null,
        ]);
    }
}
