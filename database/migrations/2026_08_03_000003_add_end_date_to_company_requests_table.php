<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tanggal berakhirnya magang, dibawa sejak pengajuan mahasiswa.
 *
 * internships.end_date sudah ada sejak awal tapi tak pernah terisi di data
 * sungguhan: ketiga jalur yang membuat magang — pengajuan yang disetujui
 * Kaprodi (dua cabang) dan Catat Magang manual — semuanya hanya menuliskan
 * start_date. Hanya perintah simulasi & akun dummy yang mengisinya. Akibatnya
 * lima halaman (profil, dashboard, detail dosen, detail industri, detail
 * kaprodi) menampilkan "Belum selesai" selamanya, bahkan untuk magang yang
 * sudah lama berakhir.
 *
 * Kolom ini menjadi tempat singgahnya: mahasiswa mengisi saat mengajukan, lalu
 * ikut terbawa ke internships ketika Kaprodi menyetujui.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_requests', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('company_requests', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });
    }
};
