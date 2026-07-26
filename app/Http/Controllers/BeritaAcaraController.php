<?php

namespace App\Http\Controllers;

use App\Models\Seminar;
use App\Models\SeminarAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Daftar hadir audiens seminar (wajib login).
 *
 * Audiens memindai QR sesi → login SIMAMA → menekan "Hadir". Kehadiran terikat
 * ke akun mahasiswa (1 akun = 1 kehadiran per sesi), sehingga jumlah audiens
 * tidak bisa digelembungkan lewat form anonim.
 */
class BeritaAcaraController extends Controller
{
    public function show(Request $request, string $token)
    {
        $seminar = Seminar::where('access_token', $token)->first();

        if (! $seminar || $seminar->status !== 'scheduled') {
            return view('public.berita-acara-closed', ['seminar' => $seminar]);
        }

        $student = Auth::user()->student;
        $already = $student && SeminarAttendance::where('seminar_id', $seminar->id)
            ->where('student_id', $student->id)->exists();

        // Anti-abuse: QR berganti tiap ~detik; tautan statis/di-share jadi kedaluwarsa.
        $rt      = $request->query('rt');
        $rtValid = $seminar->isValidRotatingToken($rt);

        $seminar->load('presenters.student.user');

        return view('public.berita-acara', compact('seminar', 'already', 'rtValid', 'rt'));
    }

    public function store(Request $request, string $token)
    {
        $seminar = Seminar::where('access_token', $token)->first();

        if (! $seminar || $seminar->status !== 'scheduled') {
            return view('public.berita-acara-closed', ['seminar' => $seminar]);
        }

        // Wajib token QR yang masih berlaku (mencegah POST langsung / tautan lama).
        if (! $seminar->isValidRotatingToken($request->input('rt'))) {
            return back()->withErrors(['hadir' => 'QR sudah berganti atau tidak valid. Pindai ulang QR terbaru yang ditampilkan dosen di layar.']);
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
