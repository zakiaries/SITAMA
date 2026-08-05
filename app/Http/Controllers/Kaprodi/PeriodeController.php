<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Kelola periode magang.
 *
 * Tak ada formulir "buat periode": tahun akademik itu fakta kalender, dibangkitkan
 * sendiri oleh sistem (Agustus–Januari Gasal, Februari–Juli Genap). Yang jadi
 * keputusan Kaprodi hanya dua, dan keduanya memang tak bisa disimpulkan dari
 * kalender: periode mana yang berjalan, dan prodi mana yang ikut magang.
 */
class PeriodeController extends Controller
{
    public function index()
    {
        // Baris periode disiapkan saat halaman dibuka, bukan lewat perintah
        // terjadwal — kalau tidak, September tahun depan Kaprodi membuka
        // halaman ini dan mendapati periodenya belum ada tanpa tahu sebabnya.
        Period::siapkanKalender();

        $periods = Period::terbaru()->withCount('students')->get();

        return view('kaprodi.periode.index', [
            'periods' => $periods,
            'prodi'   => Student::PRODI,
        ]);
    }

    /** Simpan prodi peserta sebuah periode. */
    public function prodi(Request $request, Period $period)
    {
        $request->validate([
            'study_programs'   => 'nullable|array',
            'study_programs.*' => Rule::in(Student::PRODI),
        ], [
            'study_programs.*.in' => 'Program studi tidak dikenal.',
        ]);

        $period->update(['study_programs' => $request->input('study_programs', [])]);

        return back()->with('success', "Prodi peserta {$period->label} disimpan.");
    }

    /**
     * Jadikan periode ini yang berjalan.
     *
     * Keaktifan tunggal dipaksa di model: ia menentukan periode mana yang
     * dipakai saat mahasiswa baru mendaftar, dan dua periode aktif membuat
     * penempatannya bergantung urutan baris.
     */
    public function aktifkan(Period $period)
    {
        if ($period->study_programs === []) {
            return back()->with('error',
                "Tentukan dulu prodi peserta {$period->label} sebelum mengaktifkannya.");
        }

        $period->aktifkan();

        return back()->with('success', "{$period->label} kini berjalan.");
    }
}
