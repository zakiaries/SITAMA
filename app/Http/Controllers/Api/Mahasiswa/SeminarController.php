<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use App\Models\Seminar;
use App\Models\SeminarAttendance;
use App\Models\SeminarPresenter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Jembatan API mobile seminar — model sesi-grup (menyamai web).
 *
 * Mahasiswa tidak lagi mengajukan seminar sendiri. Dosen pembimbing membuat sesi
 * dan menetapkan mahasiswa sebagai penyaji; mahasiswa mengisi ketersediaan
 * tanggal, lalu melihat jadwal final + QR daftar hadir (audiens absen via login).
 */
class SeminarController extends ApiController
{
    /** GET /mahasiswa/seminar — semua sesi di mana mahasiswa menjadi penyaji. */
    public function index(Request $request)
    {
        $student = $this->currentStudent($request);

        $rows = SeminarPresenter::where('student_id', $student->id)
            ->with(['seminar.lecturer.user', 'seminar.attendances'])
            ->get()
            ->sortByDesc(fn ($p) => $p->seminar->created_at)
            ->values();

        return response()->json([
            'sessions' => $rows->map(fn ($row) => $this->sessionCard($row)),
        ]);
    }

    /** POST /mahasiswa/seminar/{seminar}/availability — isi ketersediaan (saat draft). */
    public function submitAvailability(Request $request, Seminar $seminar)
    {
        $student = $this->currentStudent($request);

        $row = SeminarPresenter::where('seminar_id', $seminar->id)
            ->where('student_id', $student->id)->first();
        abort_unless($row, 404);

        if ($seminar->status !== 'draft') {
            return response()->json(['message' => 'Jadwal sudah ditetapkan, ketersediaan tidak dapat diubah.'], 422);
        }

        $request->validate([
            'available_dates' => 'required|string|max:255',
        ], [
            'available_dates.required' => 'Isi tanggal yang kamu bisa.',
        ]);

        $row->update([
            'available_dates' => $request->available_dates,
            'responded_at'    => now(),
        ]);

        return response()->json(['message' => 'Ketersediaan tanggalmu tersimpan.']);
    }

    /** GET /mahasiswa/seminar/{seminar} — detail sesi (penyaji + daftar hadir). */
    public function show(Request $request, Seminar $seminar)
    {
        $student = $this->currentStudent($request);
        abort_unless($this->isPresenter($seminar, $student->id), 403, 'Akses ditolak.');

        $seminar->load(['lecturer.user', 'presenters.student.user',
            'attendances' => fn ($q) => $q->orderBy('created_at')]);

        $row = $seminar->presenters->firstWhere('student_id', $student->id);

        return response()->json([
            'session'    => $this->sessionCard($row),
            'presenters' => $seminar->presenters->map(fn ($p) => [
                'name'         => $p->student->user->name ?? '-',
                'nim'          => $p->student->user->username ?? '-',
                'is_me'        => $p->student_id === $student->id,
                'responded_at' => optional($p->responded_at)->toDateTimeString(),
                'available_dates' => $p->available_dates,
            ]),
            'attendances' => $seminar->attendances->map(fn ($a) => [
                'name' => $a->name,
                'nim'  => $a->nim,
                'time' => optional($a->created_at)->toDateTimeString(),
            ]),
        ]);
    }

    /**
     * Cetak berita acara sesi ke PDF — memakai view yang sama dengan web.
     * Dilindungi signed URL (lihat route), bisa dibuka langsung di browser HP.
     */
    public function beritaAcaraPdf(Seminar $seminar)
    {
        $seminar->load(['lecturer.user', 'presenters.student.user',
            'attendances' => fn ($q) => $q->orderBy('created_at')]);

        $pdf = Pdf::loadView('mahasiswa.seminar.berita-acara-pdf', compact('seminar'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('berita-acara-seminar-' . $seminar->id . '.pdf');
    }

    /**
     * POST /mahasiswa/seminar/attend — audiens mengisi daftar hadir dengan
     * memindai QR di dalam app. Identitas terisi otomatis dari akun yang login
     * (1 akun = 1 kehadiran). Menerima {token, rt} hasil parse URL QR.
     */
    public function attend(Request $request)
    {
        $student = $this->currentStudent($request);

        $request->validate([
            'token' => 'required|string',
            'rt'    => 'required|string',
        ]);

        $seminar = Seminar::where('access_token', $request->token)->first();
        if (! $seminar || ! $seminar->daftarHadirTerbuka()) {
            return response()->json(['message' => 'Sesi seminar tidak aktif untuk daftar hadir.'], 422);
        }

        // Anti-abuse: QR berganti tiap ~detik; token lama/di-share ditolak.
        if (! $seminar->isValidRotatingToken($request->rt)) {
            return response()->json(['message' => 'QR sudah berganti atau tidak valid. Pindai ulang QR terbaru di layar.'], 422);
        }

        $attendance = SeminarAttendance::firstOrCreate(
            ['seminar_id' => $seminar->id, 'student_id' => $student->id],
            ['name' => $request->user()->name, 'nim' => $request->user()->username]
        );

        return response()->json([
            'message'       => $attendance->wasRecentlyCreated
                ? 'Daftar hadir tercatat. Terima kasih!'
                : 'Kamu sudah tercatat hadir di sesi ini.',
            'seminar_title' => $seminar->title,
            'already'       => ! $attendance->wasRecentlyCreated,
        ]);
    }

    private function isPresenter(Seminar $seminar, int $studentId): bool
    {
        return SeminarPresenter::where('seminar_id', $seminar->id)
            ->where('student_id', $studentId)->exists();
    }

    /** Kartu sesi untuk satu baris penyaji (row = SeminarPresenter milik mahasiswa). */
    private function sessionCard(SeminarPresenter $row): array
    {
        $s = $row->seminar;
        $scheduled = $s->status === 'scheduled';

        return [
            'id'               => $s->id,
            'title'            => $s->title,
            'lecturer_name'    => $s->lecturer->user->name ?? '-',
            'status'           => $s->status,
            'date'             => optional($s->date)->toDateString(),
            'time'             => $s->time,
            'location'         => $s->location,
            'guest_count'      => $s->attendances->count(),
            'min_guests'       => $s->minGuests(),
            'witnessed_at'     => optional($s->witnessed_at)->toDateTimeString(),
            // Ketersediaan yang kuisi (hanya relevan saat draft).
            'available_dates'  => $row->available_dates,
            'responded_at'     => optional($row->responded_at)->toDateTimeString(),
            // QR absensi audiens (login-based) — aktif saat terjadwal.
            'hadir_url'        => ($scheduled && $s->access_token)
                ? url('/seminar/hadir/' . $s->access_token)
                : null,
            // Unduh berita acara — untuk sesi terjadwal/selesai.
            'berita_acara_url' => in_array($s->status, ['scheduled', 'completed'], true)
                ? URL::temporarySignedRoute('mobile.seminar.berita-acara', now()->addHours(6), ['seminar' => $s->id])
                : null,
        ];
    }
}
