<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Kuota lowongan: boleh kosong, artinya "tak dibatasi".
 *
 * Kolomnya lahir dengan default 1 tapi tak pernah punya jalan masuk — form
 * Kaprodi tak memuatnya dan listingPayload() tak pernah menulisnya. Jadi setiap
 * baris yang ada bernilai 1 semata-mata karena default basis data, bukan karena
 * ada yang memutuskan begitu. Membiarkannya berarti seluruh lowongan lama
 * langsung tampak "penuh" begitu satu mahasiswa magang di perusahaan itu.
 *
 * Nilainya karena itu dikosongkan sekalian: Kaprodi yang menentukan kuota
 * sebenarnya lewat form, dan yang dibiarkan kosong tak menampilkan penanda apa
 * pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `job_listings` MODIFY `quota` INT NULL DEFAULT NULL');
        DB::table('job_listings')->update(['quota' => null]);
    }

    public function down(): void
    {
        DB::table('job_listings')->whereNull('quota')->update(['quota' => 1]);
        DB::statement('ALTER TABLE `job_listings` MODIFY `quota` INT NOT NULL DEFAULT 1');
    }
};
