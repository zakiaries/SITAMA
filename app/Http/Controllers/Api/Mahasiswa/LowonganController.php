<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use App\Models\JobListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Jembatan API mobile untuk daftar Lowongan Magang (LIHAT SAJA).
 *
 * Menyamai versi web (Mahasiswa\LowonganController): hanya menampilkan lowongan
 * aktif dari perusahaan yang BERAFILIASI dengan Polines
 * (Company.verification_status = 'verified'). Perusahaan yang belum berafiliasi
 * tidak muncul; mahasiswa tetap bisa mengajukan tempat magang apa pun lewat
 * menu "Ajukan Magang" (alur ajukan-magang TIDAK diubah).
 */
class LowonganController extends ApiController
{
    /** GET /mahasiswa/lowongan */
    public function index(Request $request): JsonResponse
    {
        $q        = trim((string) $request->get('q', ''));
        $bidang   = trim((string) $request->get('bidang', ''));
        $location = trim((string) $request->get('location', ''));

        // Basis: lowongan aktif dari perusahaan berafiliasi (verified).
        $base = JobListing::query()
            ->where('status', 'active')
            ->whereHas('company', fn ($c) => $c->where('verification_status', 'verified'));

        $listings = (clone $base)
            ->with('company')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('title', 'like', "%{$q}%")
                ->orWhere('company_name', 'like', "%{$q}%")
                ->orWhere('location', 'like', "%{$q}%")
                ->orWhere('division', 'like', "%{$q}%")
                ->orWhere('bidang', 'like', "%{$q}%")
                ->orWhereHas('company', fn ($c) => $c->where('name', 'like', "%{$q}%"))))
            ->when($bidang !== '', fn ($query) => $query->where('bidang', $bidang))
            ->when($location !== '', fn ($query) => $query->where('location', 'like', "%{$location}%"))
            ->latest()
            ->get()
            ->map(fn ($l) => $this->card($l))
            ->values();

        // Opsi filter (wilayah yang benar-benar ada di data).
        $locationOptions = (clone $base)
            ->whereNotNull('location')->where('location', '!=', '')
            ->distinct()->orderBy('location')->pluck('location')->values();

        return response()->json([
            'listings'         => $listings,
            'bidang_options'   => JobListing::BIDANG_OPTIONS,
            'location_options' => $locationOptions,
        ]);
    }

    /** GET /mahasiswa/lowongan/{jobListing} */
    public function show(JobListing $jobListing): JsonResponse
    {
        // Hanya lowongan aktif dari perusahaan berafiliasi (verified) yang boleh dibuka.
        abort_unless(
            $jobListing->status === 'active'
                && optional($jobListing->company)->verification_status === 'verified',
            404
        );

        $jobListing->load('company');

        return response()->json(['listing' => $this->detail($jobListing)]);
    }

    /** Ringkas untuk kartu daftar. */
    private function card(JobListing $l): array
    {
        return [
            'id'           => $l->id,
            'title'        => $l->title,
            'company_id'   => $l->company_id,
            'company_name' => $l->company_display_name,
            'bidang'       => $l->bidang,
            'division'     => $l->division,
            'location'     => $l->location,
            'job_type'     => $l->job_type,
        ];
    }

    /** Lengkap untuk halaman detail. */
    private function detail(JobListing $l): array
    {
        return array_merge($this->card($l), [
            'description' => $l->description,
            'skills'      => is_array($l->skills) ? $l->skills : [],
        ]);
    }
}
