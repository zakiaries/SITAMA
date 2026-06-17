<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeminarController extends Controller
{
    public function index()
    {
        $student  = Auth::user()->student;

        // Seminar wajib/umum (tidak terikat ke mahasiswa tertentu)
        $seminars = Seminar::whereNull('student_id')->with('registrations')->orderByDesc('date')->get();

        // Jadwal seminar yang diajukan mahasiswa ini sendiri
        $mySeminars = $student
            ? Seminar::where('student_id', $student->id)->with('registrations')->orderByDesc('date')->get()
            : collect();

        // Seminar hasil magang mahasiswa lain (untuk didaftari sebagai audiens)
        $peerSeminars = $student
            ? Seminar::whereNotNull('student_id')
                ->where('student_id', '!=', $student->id)
                ->where('status', 'scheduled')
                ->with(['registrations', 'student.user'])
                ->orderByDesc('date')
                ->get()
            : collect();

        $registeredIds = $student
            ? SeminarRegistration::where('student_id', $student->id)->pluck('seminar_id')->toArray()
            : [];

        $requirements = $this->seminarRequirements($student);
        $canSubmit    = !in_array(false, array_column($requirements, 'met'), true);

        return view('mahasiswa.seminar.index', compact(
            'seminars', 'mySeminars', 'peerSeminars', 'registeredIds', 'requirements', 'canSubmit'
        ));
    }

    private function seminarRequirements($student): array
    {
        $internship = $student?->activeInternship()->first();

        return [
            [
                'key'   => 'is_finished',
                'label' => 'Magang sudah ditandai selesai oleh Kaprodi',
                'met'   => (bool) ($internship?->is_finished),
                'hint'  => 'Ajukan selesai magang di halaman Magang Saya dan tunggu ACC Kaprodi.',
            ],
        ];
    }

    public function store(Request $request)
    {
        $student = Auth::user()->student;

        // Gating: semua syarat kelayakan wajib terpenuhi sebelum boleh mengajukan.
        $requirements = $this->seminarRequirements($student);
        if (in_array(false, array_column($requirements, 'met'), true)) {
            return back()->with('error', 'Anda belum memenuhi semua syarat untuk mengajukan jadwal seminar.');
        }

        $request->validate([
            'title'    => 'required|string|max:255',
            'date'     => 'required|date|after_or_equal:today',
            'time'     => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ], [
            'date.after_or_equal' => 'Tanggal seminar tidak boleh sebelum hari ini.',
        ]);

        $seminar = Seminar::create([
            'title'       => $request->title,
            'program'     => $student->study_program ?: 'Magang',
            'date'        => $request->date,
            'time'        => $request->time,
            'location'    => $request->location,
            'organizer'   => Auth::user()->name,
            'description' => $request->description,
            'status'      => 'scheduled',
            'student_id'  => $student->id,
        ]);

        // Mahasiswa otomatis terdaftar pada seminar yang ia ajukan sendiri.
        SeminarRegistration::firstOrCreate(
            ['student_id' => $student->id, 'seminar_id' => $seminar->id],
            ['status' => 'registered']
        );

        return redirect()->route('mahasiswa.seminar')
            ->with('success', 'Jadwal seminar berhasil diajukan.');
    }

    public function update(Request $request, Seminar $seminar)
    {
        $this->authorizeOwnSeminar($seminar);

        $request->validate([
            'title'    => 'required|string|max:255',
            'date'     => 'required|date|after_or_equal:today',
            'time'     => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ], [
            'date.after_or_equal' => 'Tanggal seminar tidak boleh sebelum hari ini.',
        ]);

        $seminar->update($request->only('title', 'date', 'time', 'location', 'description'));

        return redirect()->route('mahasiswa.seminar')
            ->with('success', 'Jadwal seminar berhasil diperbarui.');
    }

    public function destroy(Seminar $seminar)
    {
        $this->authorizeOwnSeminar($seminar);

        $seminar->registrations()->delete();
        $seminar->delete();

        return redirect()->route('mahasiswa.seminar')
            ->with('success', 'Pengajuan seminar dibatalkan.');
    }

    private function authorizeOwnSeminar(Seminar $seminar): void
    {
        $student = Auth::user()->student;
        if (!$student || $seminar->student_id !== $student->id) abort(403);
    }

    public function detail(Seminar $seminar)
    {
        $seminar->load(['registrations.student.user']);
        $student = Auth::user()->student;

        $isRegistered = $student
            ? SeminarRegistration::where('student_id', $student->id)
                ->where('seminar_id', $seminar->id)->exists()
            : false;

        return view('mahasiswa.seminar.detail', compact('seminar', 'isRegistered'));
    }

    public function register(Seminar $seminar)
    {
        $student = Auth::user()->student;

        $already = SeminarRegistration::where('student_id', $student->id)
            ->where('seminar_id', $seminar->id)->exists();

        if (!$already) {
            SeminarRegistration::create([
                'student_id' => $student->id,
                'seminar_id' => $seminar->id,
                'status'     => 'registered',
            ]);
        }

        return back()->with('success', 'Berhasil mendaftar seminar!');
    }
}
