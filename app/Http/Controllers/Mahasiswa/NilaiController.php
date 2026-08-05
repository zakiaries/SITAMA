<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NilaiController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        $internship = $student->activeInternship()
            ->with(['company', 'lecturer.user', 'lecturerIndustry.user'])
            ->first();

        $nilai = $internship?->nilaiSummary();

        return view('mahasiswa.nilai.index', compact('internship', 'nilai'));
    }

    /**
     * Lembar nilai untuk dicetak dan ditandatangani.
     *
     * Digerbangi nilai akhir yang sudah ada — artinya KEDUA penilai sudah
     * mengisi. Lembar setengah jadi tak ada gunanya dibawa minta tanda tangan,
     * dan yang menandatanganinya justru bisa mengira nilainya memang sebegitu.
     */
    public function pdf()
    {
        $student = Auth::user()->student;

        $internship = $student->activeInternship()
            ->with(['company', 'lecturer.user', 'lecturerIndustry.user', 'student.user', 'student.period'])
            ->first();

        $nilai = $internship?->nilaiSummary();

        if (! $internship || ($nilai['final'] ?? null) === null) {
            return back()->with('error',
                'Lembar nilai baru bisa diunduh setelah dosen pembimbing dan pembimbing industri selesai menilai.');
        }

        $pdf = Pdf::loadView('mahasiswa.nilai.pdf', [
            'internship' => $internship,
            'nilai'      => $nilai,
            'dosen'      => $internship->rincianNilai('lecturer'),
            'industri'   => $internship->rincianNilai('lecturer_industry'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('nilai-magang-' . Str::slug($student->user->name ?? 'mahasiswa') . '.pdf');
    }
}
