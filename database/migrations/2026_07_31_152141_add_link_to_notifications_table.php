<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tujuan notifikasi. Tanpa ini notifikasi hanya bisa diarahkan ke halaman
 * berdasarkan kategori — tak bisa membuka item yang dimaksud (logbook mana,
 * bimbingan mana). Disimpan sebagai path relatif, mis.
 * "/dosen/mahasiswa/3#logbook-12", supaya tak ikut rusak bila domain berubah.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('link')->nullable()->after('detail_text');
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('link');
        });
    }
};
