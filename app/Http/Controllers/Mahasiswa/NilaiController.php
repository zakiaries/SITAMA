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
     * DUA lembar terpisah, bukan satu gabungan: form resminya memang dua, dan
     * masing-masing ditandatangani orang yang berbeda lalu diserahkan
     * sendiri-sendiri. Satu lembar dengan dua ruang tanda tangan berarti
     * mahasiswa harus membawa kertas yang sama bolak-balik ke dua orang.
     *
     * Tiap lembar digerbangi penilainya SENDIRI, bukan keduanya: begitu dosen
     * selesai menilai, mahasiswa sudah bisa meminta tanda tangannya tanpa
     * menunggu pihak perusahaan — dan sebaliknya.
     */
    public function pdf(string $penilai)
    {
        abort_unless(in_array($penilai, ['dosen', 'industri'], true), 404);

        $student = Auth::user()->student;

        $internship = $student?->activeInternship()
            ->with(['company', 'lecturer.user', 'lecturerIndustry.user', 'student.user', 'student.period'])
            ->first();

        $nilai   = $internship?->nilaiSummary();
        $kunci   = $penilai === 'dosen' ? 'lecturer' : 'industry';
        $sebutan = $penilai === 'dosen' ? 'dosen pembimbing' : 'pembimbing industri';

        if (! $internship || ($nilai[$kunci]['average'] ?? null) === null) {
            return back()->with('error',
                "Lembar nilai baru bisa diunduh setelah {$sebutan} selesai menilai.");
        }

        $pdf = Pdf::loadView("mahasiswa.nilai.pdf-{$penilai}", [
            'internship' => $internship,
            'nilai'      => $nilai,
            'dosen'      => $internship->rincianNilai('lecturer'),
            'industri'   => $internship->rincianNilai('lecturer_industry'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download(
            'penilaian-magang-' . $penilai . '-' . Str::slug($student->user->name ?? 'mahasiswa') . '.pdf'
        );
    }
}
