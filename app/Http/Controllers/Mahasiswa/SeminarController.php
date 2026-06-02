<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use Illuminate\Support\Facades\Auth;

class SeminarController extends Controller
{
    public function index()
    {
        $seminars = Seminar::with('registrations')->orderByDesc('date')->get();
        $student  = Auth::user()->student;

        $registeredIds = $student
            ? SeminarRegistration::where('student_id', $student->id)->pluck('seminar_id')->toArray()
            : [];

        return view('mahasiswa.seminar.index', compact('seminars', 'registeredIds'));
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
