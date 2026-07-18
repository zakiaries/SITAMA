<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\Request;

/**
 * Daftar lowongan/tempat magang untuk mahasiswa (LIHAT SAJA).
 *
 * Hanya menampilkan lowongan dari perusahaan yang BERAFILIASI dengan Polines,
 * yaitu Company dengan verification_status = 'verified'. Perusahaan yang belum
 * berafiliasi tidak muncul di sini; mahasiswa tetap bisa mengajukan tempat magang
 * apa pun lewat menu "Ajukan Magang" (alur ajukan-magang TIDAK diubah).
 */
class LowonganController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $listings = JobListing::query()
            ->where('status', 'active')
            ->whereHas('company', fn ($c) => $c->where('verification_status', 'verified'))
            ->with('company')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('company_name', 'like', "%{$q}%")
                        ->orWhere('location', 'like', "%{$q}%")
                        ->orWhere('division', 'like', "%{$q}%")
                        ->orWhereHas('company', fn ($c) => $c->where('name', 'like', "%{$q}%"));
                });
            })
            ->latest()
            ->get();

        return view('mahasiswa.lowongan.index', compact('listings', 'q'));
    }

    public function show(JobListing $jobListing)
    {
        // Hanya lowongan aktif dari perusahaan berafiliasi (verified) yang boleh dibuka.
        abort_unless(
            $jobListing->status === 'active'
                && optional($jobListing->company)->verification_status === 'verified',
            404
        );

        $jobListing->load('company');

        return view('mahasiswa.lowongan.detail', compact('jobListing'));
    }
}
