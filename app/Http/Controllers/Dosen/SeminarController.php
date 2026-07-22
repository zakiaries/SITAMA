<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Seminar;
use App\Models\SeminarPresenter;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Sesi seminar dari sisi dosen pembimbing.
 *
 * Alur: dosen buat sesi (draft) berisi mahasiswa bimbingan yang sudah selesai
 * magang → mahasiswa mengisi ketersediaan tanggal → dosen menetapkan jadwal
 * final (scheduled) → audiens absen via login → dosen mengesahkan (completed).
 */
class SeminarController extends Controller
{
    private function lecturer()
    {
        $lecturer = Auth::user()->lecturer;
        if (! $lecturer) abort(403, 'Akses ditolak.');
        return $lecturer;
    }

    private function ownSeminar(Seminar $seminar, $lecturer): void
    {
        abort_unless($seminar->lecturer_id === $lecturer->id, 404);
    }

    public function index()
    {
        $lecturer = $this->lecturer();

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

        return view('dosen.seminar.index', compact('seminars', 'eligibleStudents'));
    }

    /** Buat sesi baru (draft) berisi mahasiswa terpilih; minta mereka isi ketersediaan. */
    public function store(Request $request)
    {
        $lecturer = $this->lecturer();

        $request->validate([
            'title'        => 'required|string|max:255',
            'student_ids'  => 'required|array|min:1',
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
            return back()->with('error', 'Tidak ada mahasiswa valid (harus bimbingan Anda & sudah selesai magang).');
        }

        $seminar = Seminar::create([
            'lecturer_id' => $lecturer->id,
            'title'       => $request->title,
            'program'     => $validStudents->first()->study_program ?: 'Magang',
            'organizer'   => Auth::user()->name,
            'status'      => 'draft',
        ]);

        foreach ($validStudents as $student) {
            SeminarPresenter::create([
                'seminar_id' => $seminar->id,
                'student_id' => $student->id,
            ]);

            if ($student->user) {
                Notification::create([
                    'user_id'     => $student->user->id,
                    'message'     => 'Dosen membuka penjadwalan seminar: ' . $seminar->title,
                    'date'        => now()->toDateString(),
                    'category'    => 'seminar',
                    'is_read'     => false,
                    'detail_text' => 'Isi ketersediaan tanggalmu di menu Seminar agar dosen bisa menetapkan jadwal.',
                ]);
            }
        }

        return back()->with('success', 'Sesi seminar dibuat. Mahasiswa diminta mengisi ketersediaan tanggal.');
    }

    /** Tetapkan jadwal final → sesi menjadi scheduled + aktifkan QR daftar hadir. */
    public function finalize(Request $request, Seminar $seminar)
    {
        $lecturer = $this->lecturer();
        $this->ownSeminar($seminar, $lecturer);

        if (! in_array($seminar->status, ['draft', 'scheduled'], true)) {
            return back()->with('error', 'Sesi ini tidak dapat dijadwalkan lagi.');
        }

        $request->validate([
            'date'     => 'required|date|after_or_equal:today',
            'time'     => 'nullable|string|max:50',
            'location' => 'required|string|max:255',
        ], [
            'date.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
            'location.required'   => 'Ruang/tempat wajib diisi.',
        ]);

        $seminar->update([
            'date'         => $request->date,
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

        return back()->with('success', 'Jadwal seminar ditetapkan. Mahasiswa penyaji telah diberi tahu.');
    }

    /** Sahkan sesi (dosen sebagai saksi) → completed. Butuh audiens minimal. */
    public function sahkan(Seminar $seminar)
    {
        $lecturer = $this->lecturer();
        $this->ownSeminar($seminar, $lecturer);

        if ($seminar->status !== 'scheduled') {
            return back()->with('error', 'Hanya sesi terjadwal yang bisa disahkan.');
        }

        if ($seminar->guestCount() < Seminar::MIN_GUESTS) {
            return back()->with('error', 'Belum memenuhi minimal ' . Seminar::MIN_GUESTS
                . ' audiens (' . $seminar->guestCount() . ' hadir). Sesi belum bisa disahkan.');
        }

        $seminar->update(['status' => 'completed', 'witnessed_at' => now()]);

        $this->notifyPresenters(
            $seminar,
            'Seminar disahkan selesai: ' . $seminar->title,
            'Dosen pembimbing telah mengesahkan seminarmu telah berlangsung dan selesai.'
        );

        return back()->with('success', 'Seminar disahkan selesai.');
    }

    /** Batalkan/hapus sesi yang belum selesai. */
    public function destroy(Seminar $seminar)
    {
        $lecturer = $this->lecturer();
        $this->ownSeminar($seminar, $lecturer);

        if ($seminar->status === 'completed') {
            return back()->with('error', 'Sesi yang sudah disahkan tidak dapat dihapus.');
        }

        $seminar->presenters()->delete();
        $seminar->attendances()->delete();
        $seminar->delete();

        return back()->with('success', 'Sesi seminar dibatalkan.');
    }

    private function notifyPresenters(Seminar $seminar, string $message, string $detail): void
    {
        $seminar->loadMissing('presenters.student.user');
        foreach ($seminar->presenters as $p) {
            $userId = $p->student?->user?->id;
            if ($userId) {
                Notification::create([
                    'user_id'     => $userId,
                    'message'     => $message,
                    'date'        => now()->toDateString(),
                    'category'    => 'seminar',
                    'is_read'     => false,
                    'detail_text' => $detail,
                ]);
            }
        }
    }
}
