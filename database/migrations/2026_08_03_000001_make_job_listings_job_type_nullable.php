<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tipe lowongan boleh kosong.
 *
 * Kolomnya dibuat NOT NULL dengan default 'On-site', tapi form Kaprodi
 * memperlakukan "Tipe" sebagai isian opsional dan listingPayload() selalu
 * menuliskan nilainya — jadi mengosongkan kolom itu mengirim NULL ke kolom yang
 * menolak NULL, dan Kaprodi mendapat error 500 saat menambah maupun menyunting
 * lowongan.
 *
 * Kolomnya dibuat nullable agar "kosong" bisa disimpan apa adanya, sejalan
 * dengan isian opsional lain di form yang sama (divisi, lokasi, bidang).
 * Memaksakan default 'On-site' akan mengarang data yang tak diisi siapa pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `job_listings` MODIFY `job_type` VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::table('job_listings')->whereNull('job_type')->update(['job_type' => 'On-site']);
        DB::statement("ALTER TABLE `job_listings` MODIFY `job_type` VARCHAR(255) NOT NULL DEFAULT 'On-site'");
    }
};
