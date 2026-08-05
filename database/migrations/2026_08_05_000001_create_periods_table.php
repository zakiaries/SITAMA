<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Periode magang: satu semester dari satu tahun akademik.
 *
 * Sebelum ini tak ada tempat menyimpan "siapa yang magang tahun ini". Yang ada
 * hanya `students.academic_year` — teks bebas yang DIKETIK SENDIRI oleh
 * mahasiswa saat mendaftar. Akibatnya penyaring tahun di dashboard Kaprodi
 * menyodorkan nilai seperti "2023/2026", rentang tiga tahun yang bukan tahun
 * akademik, dan daftar mahasiswa menumpuk lintas angkatan tanpa bisa dipisah.
 *
 * Satuannya SEMESTER, bukan tahun, karena magang berlangsung 5 bulan sehingga
 * pas satu semester: Gasal Agustus–Januari, Genap Februari–Juli. Kalau
 * satuannya tahun, yang berangkat Agustus dan yang berangkat Februari tetap
 * tercampur — masalah yang sama, hanya lebih kecil.
 *
 * `semester` sengaja string, bukan enum: Simadu Polines juga mengenal
 * "Matrikulasi" di beberapa tahun. Belum dipakai untuk magang, tapi
 * menambahkannya kelak tak perlu ALTER pada enum.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 9);  // "2026/2027"
            $table->string('semester', 20);      // gasal | genap
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Lama magang wajib. Disimpan per periode, bukan sebagai tetapan di
            // kode, supaya kebijakan yang berubah tak menuntut deploy ulang.
            $table->unsignedTinyInteger('duration_months')->default(5);

            // Prodi peserta. Disimpan di sini karena yang magang berganti tiap
            // tahun: 2025/2026 Genap giliran Teknik Informatika, 2026/2027
            // Gasal giliran Teknologi Rekayasa Komputer. Kosong = belum
            // dibatasi, semua prodi boleh.
            $table->json('study_programs')->nullable();

            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
