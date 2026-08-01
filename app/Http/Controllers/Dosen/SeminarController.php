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
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

    /**
     * Halaman QR daftar hadir untuk diproyeksikan dosen di layar. QR memuat token
     * berbasis waktu yang berganti otomatis (halaman auto-refresh) → tautan yang
     * di-share/di-screenshot cepat kedaluwarsa, mencegah titip absen dari luar ruangan.
     */
    public function qr(Seminar $seminar)
    {
        $lecturer = $this->lecturer();
        $this->ownSeminar($seminar, $lecturer);

        if ($seminar->status !== 'scheduled' || ! $seminar->access_token) {
            return back()->with('error', 'QR daftar hadir hanya tersedia untuk sesi yang sudah dijadwalkan.');
        }

        if ($seminar->jadwalSudahLewat()) {
            return back()->with('error', 'Tanggal seminar sudah lewat — daftar hadir ditutup. Sesi tinggal disahkan.');
        }

        $url   = url('/seminar/hadir/' . $seminar->access_token) . '?rt=' . $seminar->rotatingToken();
        $qrSvg = QrCode::format('svg')->size(320)->margin(1)->generate($url);

        return view('dosen.seminar.qr', [
            'seminar'  => $seminar,
            'qrSvg'    => $qrSvg,
            'guests'   => $seminar->attendances()->count(),
            'interval' => Seminar::QR_INTERVAL,
        ]);
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

            Notification::kirim(
                $student->user?->id,
                'Dosen membuka penjadwalan seminar: ' . $seminar->title,
                'seminar',
                'Isi ketersediaan tanggalmu di menu Seminar agar dosen bisa menetapkan jadwal.',
                "/mahasiswa/seminar/{$seminar->id}"
            );
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

        if ($seminar->jadwalSudahLewat()) {
            return back()->with('error', 'Tanggal seminar sudah lewat — jam dan ruang tidak bisa diubah lagi. Sesi tinggal disahkan.');
        }

        // Tanggal ditetapkan SEKALI saat penjadwalan awal. Sesi yang sudah
        // terjadwal hanya boleh diubah jam dan lokasinya — mengganti tanggal
        // membatalkan kesiapan penyaji dan audiens yang sudah diberi tahu.
        $sudahTerjadwal = $seminar->status === 'scheduled';

        $aturan = [
            'time'     => 'nullable|string|max:50',
            'location' => 'required|string|max:255',
        ];

        if (! $sudahTerjadwal) {
            $aturan['date'] = 'required|date|after_or_equal:today';
        }

        $request->validate($aturan, [
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

        return back()->with('success', 'Jadwal seminar ditetapkan. Mahasiswa penyaji telah diberi tahu.');
    }

    /** Ubah detail deskriptif sesi (judul & deskripsi). Jadwal/lokasi lewat finalize. */
    public function update(Request $request, Seminar $seminar)
    {
        $lecturer = $this->lecturer();
        $this->ownSeminar($seminar, $lecturer);

        if (! in_array($seminar->status, ['draft', 'scheduled'], true)) {
            return back()->with('error', 'Sesi yang sudah disahkan atau dibatalkan tidak bisa diubah.');
        }

        if ($seminar->jadwalSudahLewat()) {
            return back()->with('error', 'Tanggal seminar sudah lewat — detail sesi tidak bisa diubah lagi. Sesi tinggal disahkan.');
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $seminar->update([
            'title'       => $request->title,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Detail sesi seminar diperbarui.');
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
            Notification::kirim(
                $p->student?->user?->id, $message, 'seminar', $detail,
                "/mahasiswa/seminar/{$seminar->id}"
            );
        }
    }
}
