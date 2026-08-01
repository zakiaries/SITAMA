<?php

namespace App\Http\Controllers\Concerns;

/**
 * Menutup jalur ubah-data mahasiswa begitu pengajuan selesai magang dikirim.
 *
 * Pengajuan selesai magang digerbangi checklist kelengkapan (sertifikat,
 * laporan di-ACC, nilai kedua pembimbing, minimal logbook). Kalau isinya masih
 * bisa diubah setelah pengajuan dikirim, yang diperiksa Kaprodi bisa berbeda
 * dari yang diajukan — sertifikat bisa ditukar, logbook dikurangi, bimbingan
 * ditambah belakangan.
 *
 * Dipakai bersama oleh sertifikat, logbook, dan bimbingan agar aturannya satu
 * dan pesannya seragam.
 */
trait MengunciSaatSelesaiMagang
{
    /**
     * @param  \App\Models\Student|null  $student
     * @return string|null  null bila masih boleh diubah.
     */
    protected function alasanMagangTerkunci($student): ?string
    {
        $internship = $student?->activeInternship()->first();

        return $internship?->terkunciUntukMahasiswa() ? $internship->alasanTerkunci() : null;
    }

    /**
     * Versi web.
     *
     * @return \Illuminate\Http\RedirectResponse|null  null bila masih boleh diubah.
     */
    protected function tolakBilaTerkunci($student)
    {
        $alasan = $this->alasanMagangTerkunci($student);

        return $alasan ? back()->with('error', $alasan) : null;
    }

    /**
     * Versi API. Wajib ada: tanpa ini kunci di web bisa ditembus lewat aplikasi
     * HP yang memanggil endpoint yang sama.
     *
     * @return \Illuminate\Http\JsonResponse|null  null bila masih boleh diubah.
     */
    protected function tolakBilaTerkunciJson($student)
    {
        $alasan = $this->alasanMagangTerkunci($student);

        return $alasan ? response()->json(['message' => $alasan], 422) : null;
    }
}
