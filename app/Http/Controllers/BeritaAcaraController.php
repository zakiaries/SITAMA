<?php

namespace App\Http\Controllers;

use App\Models\Seminar;
use App\Models\SeminarAttendance;
use Illuminate\Support\Facades\Auth;

/**
 * Daftar hadir audiens seminar (wajib login).
 *
 * Audiens memindai QR sesi → login SITAMA → menekan "Hadir". Kehadiran terikat
 * ke akun mahasiswa (1 akun = 1 kehadiran per sesi), sehingga jumlah audiens
 * tidak bisa digelembungkan lewat form anonim.
 */
class BeritaAcaraController extends Controller
{
    public function show(string $token)
    {
        $seminar = Seminar::where('access_token', $token)->first();

        if (! $seminar || $seminar->status !== 'scheduled') {
            return view('public.berita-acara-closed', ['seminar' => $seminar]);
        }

        $student = Auth::user()->student;
        $already = $student && SeminarAttendance::where('seminar_id', $seminar->id)
            ->where('student_id', $student->id)->exists();

        $seminar->load('presenters.student.user');

        return view('public.berita-acara', compact('seminar', 'already'));
    }

    public function store(string $token)
    {
        $seminar = Seminar::where('access_token', $token)->first();

        if (! $seminar || $seminar->status !== 'scheduled') {
            return view('public.berita-acara-closed', ['seminar' => $seminar]);
        }

        $student = Auth::user()->student;
        if (! $student) {
            return back()->withErrors(['hadir' => 'Hanya akun mahasiswa yang dapat mengisi daftar hadir.']);
        }

        SeminarAttendance::firstOrCreate(
            ['seminar_id' => $seminar->id, 'student_id' => $student->id],
            ['name' => Auth::user()->name, 'nim' => Auth::user()->username]
        );

        return view('public.berita-acara-success', compact('seminar'));
    }
}
