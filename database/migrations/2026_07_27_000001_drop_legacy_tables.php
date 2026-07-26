<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Buang tabel warisan alur lama (lamaran/kelompok magang) yang sudah tidak dipakai
 * fitur mana pun. Membersihkan DB lama maupun hasil migrate:fresh.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Urut anak → induk agar tak melanggar foreign key.
        Schema::dropIfExists('internship_group_members');
        Schema::dropIfExists('internship_groups');
        Schema::dropIfExists('applications');
    }

    public function down(): void
    {
        // Tidak dibuat ulang: alur ini sudah dihapus permanen.
    }
};
