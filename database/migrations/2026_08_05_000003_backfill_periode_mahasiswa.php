<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tempatkan 89 mahasiswa yang sudah ada ke periodenya masing-masing.
 *
 * Tanpa ini kolom `period_id` lahir kosong seluruhnya, dan penyaring periode
 * yang menyusul akan menampilkan daftar hampa untuk data yang sebenarnya ada.
 *
 * Pembaginya `academic_year = '2026/2027'` — satu-satunya nilai yang tepercaya,
 * karena diisi perintah `simama:impor-peserta`, bukan diketik mahasiswa. Ia
 * kebetulan berimpit sempurna dengan awalan NIM: 76 peserta impor semuanya
 * `4.33` (D4 Teknologi Rekayasa Komputer), 13 sisanya `3.34` (D3 Teknik
 * Informatika angkatan 2023) yang magang Maret–April 2026. Dua penanda berbeda
 * yang sepakat, jadi pembagiannya bukan tebakan.
 *
 * academic_year ke-13 mahasiswa itu berisi "2023/2026", "2023/2024", dan
 * "2025/2026" — tiga nilai berbeda untuk satu angkatan yang sama, bukti bahwa
 * kolom teks bebas itu memang tak bisa dijadikan patokan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Instalasi baru tak punya apa pun untuk dipindahkan; periode pertamanya
        // dibuat Kaprodi lewat halaman Periode. Penjaga ini sekaligus menjaga DB
        // tes tetap bersih dari periode bawaan.
        if (DB::table('students')->count() === 0) {
            return;
        }

        $now = now();

        $gasal = $this->periode(
            '2026/2027', 'gasal', '2026-08-01', '2027-01-31',
            ['Teknologi Rekayasa Komputer'], true, $now
        );

        $genap = $this->periode(
            '2025/2026', 'genap', '2026-02-01', '2026-07-31',
            ['Teknik Informatika'], false, $now
        );

        DB::table('students')->where('academic_year', '2026/2027')
            ->update(['period_id' => $gasal]);

        DB::table('students')->whereNull('period_id')
            ->update(['period_id' => $genap]);
    }

    public function down(): void
    {
        DB::table('students')->update(['period_id' => null]);

        DB::table('periods')
            ->whereIn('academic_year', ['2026/2027', '2025/2026'])
            ->delete();
    }

    /** Buat periode bila belum ada, lalu kembalikan id-nya. */
    private function periode(
        string $tahun,
        string $semester,
        string $mulai,
        string $selesai,
        array $prodi,
        bool $aktif,
        $now
    ): int {
        $id = DB::table('periods')
            ->where('academic_year', $tahun)
            ->where('semester', $semester)
            ->value('id');

        return $id ?: DB::table('periods')->insertGetId([
            'academic_year'   => $tahun,
            'semester'        => $semester,
            'start_date'      => $mulai,
            'end_date'        => $selesai,
            'duration_months' => 5,
            'study_programs'  => json_encode($prodi),
            'is_active'       => $aktif,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
    }
};
