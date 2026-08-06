<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Dua perbaikan kata kunci pada basis pengetahuan chatbot.
 *
 * Seeder-nya idempoten lewat firstOrCreate, sehingga entri yang sudah telanjur
 * ada di basis data tidak ikut berubah ketika daftar bawaannya disunting.
 * Migrasi ini yang menyusulkannya ke peladen yang sudah berjalan.
 *
 * 1. Entri absensi QR mendapat kata "absen". Stemmer tidak memotong akhiran
 *    -si, sehingga "absen" dan "absensi" tidak pernah bertemu; pertanyaan
 *    "Absen seminar pakai apa?" kehilangan kata pembedanya dan menyusut menjadi
 *    "seminar" saja, lalu dijawab entri seminar yang lain.
 *
 * 2. Entri jumlah audiens memuat kata "seminar" dua kali. Pengulangan itu
 *    menaikkan bobot TF-nya sehingga entri tersebut memenangi setiap kueri yang
 *    hanya menyisakan kata "seminar" — termasuk "Kapan saya boleh seminar?",
 *    yang mestinya dijawab entri syarat seminar.
 *
 * Ditulis defensif: hanya menyentuh entri yang isinya masih seperti bawaan,
 * supaya suntingan Kaprodi lewat menu Kelola Basis Pengetahuan tidak tertimpa.
 */
return new class extends Migration
{
    public function up(): void
    {
        $absensi = DB::table('chatbot_knowledges')
            ->where('pertanyaan', 'Bagaimana absensi seminar dengan QR Code dan berita acara?')
            ->first();

        if ($absensi && ! preg_match('/\babsen\b/', $absensi->kata_kunci)) {
            DB::table('chatbot_knowledges')->where('id', $absensi->id)->update([
                'kata_kunci' => str_replace('absensi', 'absensi absen', $absensi->kata_kunci),
                'updated_at' => now(),
            ]);
        }

        $audiens = DB::table('chatbot_knowledges')
            ->where('pertanyaan', 'Berapa jumlah audiens minimal untuk seminar dan bagaimana mendaftarnya?')
            ->first();

        if ($audiens && substr_count($audiens->kata_kunci, 'seminar') > 1) {
            // Buang kemunculan kedua saja; yang pertama tetap perlu.
            $posisi = strpos($audiens->kata_kunci, 'seminar',
                strpos($audiens->kata_kunci, 'seminar') + 1);

            DB::table('chatbot_knowledges')->where('id', $audiens->id)->update([
                'kata_kunci' => preg_replace('/\s+/', ' ', trim(
                    substr_replace($audiens->kata_kunci, '', $posisi, strlen('seminar')))),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $absensi = DB::table('chatbot_knowledges')
            ->where('pertanyaan', 'Bagaimana absensi seminar dengan QR Code dan berita acara?')
            ->first();

        if ($absensi) {
            DB::table('chatbot_knowledges')->where('id', $absensi->id)->update([
                'kata_kunci' => str_replace('absensi absen', 'absensi', $absensi->kata_kunci),
            ]);
        }

        $audiens = DB::table('chatbot_knowledges')
            ->where('pertanyaan', 'Berapa jumlah audiens minimal untuk seminar dan bagaimana mendaftarnya?')
            ->first();

        if ($audiens && substr_count($audiens->kata_kunci, 'seminar') === 1) {
            DB::table('chatbot_knowledges')->where('id', $audiens->id)->update([
                'kata_kunci' => str_replace('mendaftar mahasiswa', 'mendaftar seminar mahasiswa',
                    $audiens->kata_kunci),
            ]);
        }
    }
};
