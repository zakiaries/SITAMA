<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Program studi dosen.
 *
 * Sebelumnya tak disimpan sama sekali: opsi --prodi di simama:impor-dosen hanya
 * memilih daftar mana yang diulang dan label apa yang dicetak ke terminal, lalu
 * akunnya dibuat dengan `Lecturer::create(['user_id' => ...])` saja.
 *
 * Akibatnya halaman Data Dosen Kaprodi tak bisa memisahkan dosen D3 Teknik
 * Informatika dari D4 Teknologi Rekayasa Komputer — Kaprodi harus menggulir 26
 * kartu dan mengingat sendiri siapa mengajar di mana.
 *
 * Nullable karena dosen yang lahir dari jalur lain (form Tambah Dosen, impor
 * peserta) belum tentu diketahui prodinya; yang kosong ditampilkan sebagai
 * "Belum diisi" dan tetap bisa disaring.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            $table->string('study_program')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            $table->dropColumn('study_program');
        });
    }
};
