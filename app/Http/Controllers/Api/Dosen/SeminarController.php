<?php

namespace App\Http\Controllers\Api\Dosen;

use App\Http\Controllers\Api\ApiController;
use App\Models\Notification;
use App\Models\Seminar;
use App\Models\SeminarPresenter;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Jembatan API mobile "Seminar Magang" untuk dosen — menyamai web
 * (Dosen\SeminarController): dosen buat sesi (draft) berisi mahasiswa bimbingan
 * yang sudah selesai magang → mahasiswa isi ketersediaan → dosen finalkan
 * jadwal (scheduled) → audiens absen via login → dosen sahkan (completed).
 */
class SeminarController extends ApiController
{
    /** GET /dosen/seminar — daftar sesi + mahasiswa yang bisa dijadikan penyaji. */
    public function index(Request $request)
    {
        $lecturer = $this->currentLecturer($request, 'lecturer');

        $seminars = Seminar::where('lecturer_id', $lecturer->id)
            ->with(['presenters.student.user', 'attendances'])
            ->orderByRaw("FIELD(status,'draft','scheduled','completed','cancelled')")
            ->orderByDesc('date')
            ->get();

        // Mahasiswa bimbingan yang sudah selesai magang & belum masuk sesi aktif.
        $busyStudentIds = SeminarPresenter::whereHas('seminar', fn ($q) => $q
            ->where('lecturer_id', $lecturer->id)
            ->whereIn('status', ['draft', 'scheduled']))
            ->pluck('student_id')->all();

        $eligibleStudents = Student::where('lecturer_id', $lecturer->id)
            ->whereHas('internships', fn ($q) => $q->where('is_finished', true))
            ->whereNotIn('id', $busyStudentIds)
            ->with('user')
            ->get();

        return response()->json([
            'sessions'          => $seminars->map(fn ($s) => $this->card($s)),
            'eligible_students' => $eligibleStudents->map(fn ($st) => [
                'id'   => $st->id,
                'name' => $st->user->name ?? '-',
                'nim'  => $st->user->username ?? '-',
            ]),
            'min_guests'        => Seminar::MIN_GUESTS,
        ]);
    }

    /** POST /dosen/seminar — buat sesi (draft) + notifikasi isi ketersediaan. */
    public function store(Request $request)
    {
        $lecturer = $this->currentLecturer($request, 'lecturer');

        $request->validate([
            'title'         => 'required|string|max:255',
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'integer',
        ], [
            'student_ids.required' => 'Pilih minimal satu mahasiswa penyaji.',
        ]);

        // Hanya mahasiswa bimbingan sendiri yang sudah selesai magang.
        $validStudents = Student::where('lecturer_id', $lecturer->id)
            ->whereIn('id', $request->student_ids)
            ->whereHas('internships', fn ($q) => $q->where('is_finished', true))
            ->with('user')
            ->get();

        if ($validStudents->isEmpty()) {
            return response()->json(['message' => 'Tidak ada mahasiswa valid (harus bimbingan Anda & sudah selesai magang).'], 422);
        }

        $seminar = Seminar::create([
            'lecturer_id' => $lecturer->id,
            'title'       => $request->title,
            'program'     => $validStudents->first()->study_program ?: 'Magang',
            'organizer'   => $request->user()->name,
            'status'      => 'draft',
        ]);

        foreach ($validStudents as $student) {
            SeminarPresenter::create([
                'seminar_id' => $seminar->id,
                'student_id' => $student->id,
            ]);

            Notification::kirim(
                $student->user?->id,
                'Dosen membuka penjadwalan seminar: ' . $seminar->title,
                'seminar',
                'Isi ketersediaan tanggalmu di menu Seminar agar dosen bisa menetapkan jadwal.',
                "/mahasiswa/seminar/{$seminar->id}"
            );
        }

        return response()->json(['message' => 'Sesi seminar dibuat. Mahasiswa diminta mengisi ketersediaan tanggal.', 'id' => $seminar->id], 201);
    }

    /** POST /dosen/seminar/{seminar}/finalize — tetapkan jadwal final (scheduled). */
    public function finalize(Request $request, Seminar $seminar)
    {
        $lecturer = $this->currentLecturer($request, 'lecturer');
        $this->ownSeminar($seminar, $lecturer);

        if (! in_array($seminar->status, ['draft', 'scheduled'], true)) {
            return response()->json(['message' => 'Sesi ini tidak dapat dijadwalkan lagi.'], 422);
        }

        // Paritas dengan web: lewat hari-H, jadwal & ruang dikunci.
        if ($seminar->jadwalSudahLewat()) {
            return response()->json(['message' => 'Tanggal seminar sudah lewat — jam dan ruang tidak bisa diubah lagi. Sesi tinggal disahkan.'], 422);
        }

        // Paritas dengan web: tanggal ditetapkan sekali saat penjadwalan awal.
        // Sesi yang sudah terjadwal hanya boleh diubah jam dan lokasinya.
        $sudahTerjadwal = $seminar->status === 'scheduled';

        $request->validate([
            'date'     => ($sudahTerjadwal ? 'nullable' : 'required') . '|date|after_or_equal:today',
            'time'     => 'nullable|string|max:50',
            'location' => 'required|string|max:255',
        ], [
            'date.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
            'location.required'   => 'Ruang/tempat wajib diisi.',
        ]);

        $seminar->update([
            'date'         => $sudahTerjadwal ? $seminar->date : $request->date,
            'time'         => $request->time,
            'location'     => $request->location,
            'status'       => 'scheduled',
            'access_token' => $seminar->access_token ?: Str::random(48),
        ]);

        $this->notifyPresenters(
            $seminar,
            'Jadwal seminar ditetapkan: ' . $seminar->title,
            'Tanggal ' . $seminar->date->format('d M Y') . ($seminar->time ? ' pukul ' . $seminar->time : '')
                . ' di ' . $seminar->location . '.'
        );

        return response()->json(['message' => 'Jadwal seminar ditetapkan. Mahasiswa penyaji telah diberi tahu.']);
    }

    /** POST /dosen/seminar/{seminar}/sahkan — sahkan sesi selesai (butuh audiens minimal). */
    public function sahkan(Request $request, Seminar $seminar)
    {
        $lecturer = $this->currentLecturer($request, 'lecturer');
        $this->ownSeminar($seminar, $lecturer);

        if ($seminar->status !== 'scheduled') {
            return response()->json(['message' => 'Hanya sesi terjadwal yang bisa disahkan.'], 422);
        }

        if ($seminar->guestCount() < Seminar::MIN_GUESTS) {
            return response()->json(['message' => 'Belum memenuhi minimal ' . Seminar::MIN_GUESTS
                . ' audiens (' . $seminar->guestCount() . ' hadir). Sesi belum bisa disahkan.'], 422);
        }

        $seminar->update(['status' => 'completed', 'witnessed_at' => now()]);

        $this->notifyPresenters(
            $seminar,
            'Seminar disahkan selesai: ' . $seminar->title,
            'Dosen pembimbing telah mengesahkan seminarmu telah berlangsung dan selesai.'
        );

        return response()->json(['message' => 'Seminar disahkan selesai.']);
    }

    /** DELETE /dosen/seminar/{seminar} — batalkan/hapus sesi yang belum selesai. */
    public function destroy(Request $request, Seminar $seminar)
    {
        $lecturer = $this->currentLecturer($request, 'lecturer');
        $this->ownSeminar($seminar, $lecturer);

        if ($seminar->status === 'completed') {
            return response()->json(['message' => 'Sesi yang sudah disahkan tidak dapat dihapus.'], 422);
        }

        $seminar->presenters()->delete();
        $seminar->attendances()->delete();
        $seminar->delete();

        return response()->json(['message' => 'Sesi seminar dibatalkan.']);
    }

    /** PUT /dosen/seminar/{seminar} — ubah judul & deskripsi sesi (draft/scheduled). */
    public function update(Request $request, Seminar $seminar)
    {
        $lecturer = $this->currentLecturer($request, 'lecturer');
        $this->ownSeminar($seminar, $lecturer);

        if (! in_array($seminar->status, ['draft', 'scheduled'], true)) {
            return response()->json(['message' => 'Sesi yang sudah disahkan atau dibatalkan tidak bisa diubah.'], 422);
        }

        if ($seminar->jadwalSudahLewat()) {
            return response()->json(['message' => 'Tanggal seminar sudah lewat — detail sesi tidak bisa diubah lagi. Sesi tinggal disahkan.'], 422);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $seminar->update(['title' => $request->title, 'description' => $request->description]);

        return response()->json(['message' => 'Detail sesi seminar diperbarui.']);
    }

    /** GET /dosen/seminar/{seminar}/qr — token QR rotating terkini utk ditampilkan dosen. */
    public function qrToken(Request $request, Seminar $seminar)
    {
        $lecturer = $this->currentLecturer($request, 'lecturer');
        $this->ownSeminar($seminar, $lecturer);

        if ($seminar->status !== 'scheduled' || ! $seminar->access_token) {
            return response()->json(['message' => 'QR hanya tersedia untuk sesi terjadwal.'], 422);
        }

        if ($seminar->jadwalSudahLewat()) {
            return response()->json(['message' => 'Tanggal seminar sudah lewat — daftar hadir ditutup. Sesi tinggal disahkan.'], 422);
        }

        $rt = $seminar->rotatingToken();

        return response()->json([
            'url'         => url('/seminar/hadir/' . $seminar->access_token) . '?rt=' . $rt,
            'rt'          => $rt,
            'interval'    => Seminar::QR_INTERVAL,
            'guest_count' => $seminar->attendances()->count(),
            'min_guests'  => Seminar::MIN_GUESTS,
        ]);
    }

    private function ownSeminar(Seminar $seminar, $lecturer): void
    {
        abort_unless($seminar->lecturer_id === $lecturer->id, 404);
    }

    private function notifyPresenters(Seminar $seminar, string $message, string $detail): void
    {
        $seminar->loadMissing('presenters.student.user');
        foreach ($seminar->presenters as $p) {
            Notification::kirim(
                $p->student?->user?->id, $message, 'seminar', $detail,
                "/mahasiswa/seminar/{$seminar->id}"
            );
        }
    }

    /** Kartu sesi untuk dosen: status + penyaji + progres audiens. */
    private function card(Seminar $s): array
    {
        return [
            'id'           => $s->id,
            'title'        => $s->title,
            'description'  => $s->description,
            'status'       => $s->status,
            'date'         => optional($s->date)->toDateString(),
            'time'         => $s->time,
            'location'     => $s->location,
            'guest_count'  => $s->attendances->count(),
            'min_guests'   => Seminar::MIN_GUESTS,
            'witnessed_at' => optional($s->witnessed_at)->toDateTimeString(),
            // Lewat hari-H sesi dikunci: klien sebaiknya menyembunyikan form ubah.
            'date_passed'  => $s->jadwalSudahLewat(),
            'can_edit'     => in_array($s->status, ['draft', 'scheduled'], true) && ! $s->jadwalSudahLewat(),
            // Sertakan token rotating terkini agar QR yang ditampilkan valid saat dipindai.
            // Untuk QR yang berputar otomatis, klien memanggil endpoint qr secara berkala.
            'hadir_url'    => ($s->daftarHadirTerbuka() && $s->access_token)
                ? url('/seminar/hadir/' . $s->access_token) . '?rt=' . $s->rotatingToken()
                : null,
            'presenters'   => $s->presenters->map(fn ($p) => [
                'name'            => $p->student->user->name ?? '-',
                'nim'             => $p->student->user->username ?? '-',
                'available_dates' => $p->available_dates,
                'responded_at'    => optional($p->responded_at)->toDateTimeString(),
            ]),
        ];
    }
}
