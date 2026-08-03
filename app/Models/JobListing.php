<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    use HasFactory;

    /**
     * Pilihan bidang/role baku — dipakai form (mahasiswa & kaprodi), filter,
     * dan sebagai dasar rekomendasi chatbot. Ubah di sini agar konsisten.
     */
    public const BIDANG_OPTIONS = [
        'Front End Developer',
        'Back End Developer',
        'Full Stack Developer',
        'Mobile Developer',
        'UI/UX Design',
        'Data / Data Science',
        'Jaringan & Sistem',
        'Cyber Security',
        'Multimedia / Editor',
        'IT Support',
        'Lainnya',
    ];

    protected $fillable = [
        'company_id', 'company_name', 'title', 'division', 'bidang', 'description', 'skills',
        'location', 'job_type', 'quota', 'duration_months',
        'pic_email', 'pic_name', 'pic_phone', 'status',
    ];

    protected $casts = [
        'skills' => 'array',
        'quota'  => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Magang yang berjalan di perusahaan lowongan ini.
     *
     * SIMAMA tak punya alur "melamar lowongan": mahasiswa mencari tempat magang
     * sendiri lalu mengunggah bukti penerimaan, jadi tak ada kolom yang mengikat
     * sebuah magang ke lowongan tertentu. Yang bisa dihitung hanyalah magang di
     * perusahaan yang sama — kalau satu perusahaan punya beberapa lowongan,
     * angkanya sama untuk semuanya. Itu sebabnya kuota di sini penanda, bukan
     * pembatas.
     *
     * Magang yang sudah selesai tak ikut dihitung: tempatnya kembali kosong.
     */
    public function magangBerjalan()
    {
        return $this->hasMany(Internship::class, 'company_id', 'company_id')
            ->where('is_finished', false);
    }

    /** Berapa tempat yang sedang terpakai. */
    public function jumlahTerisi(): int
    {
        // Dipakai apa adanya bila controller sudah menghitungnya lewat withCount,
        // supaya daftar lowongan tak memicu satu query per baris.
        return (int) ($this->magang_berjalan_count ?? $this->magangBerjalan()->count());
    }

    /** Kuota hanya bermakna bila Kaprodi mengisinya dengan angka positif. */
    public function punyaKuota(): bool
    {
        return $this->quota !== null && $this->quota > 0;
    }

    public function penuh(): bool
    {
        return $this->punyaKuota() && $this->jumlahTerisi() >= $this->quota;
    }

    /**
     * Ringkasan kuota, mis. "2 dari 3 mahasiswa di perusahaan ini".
     *
     * Kalimatnya sengaja menyebut PERUSAHAAN, bukan lowongan. Dulu berbunyi
     * "2 dari 3 terisi", yang terbaca seolah menghitung pelamar lowongan ini —
     * padahal yang dihitung mahasiswa yang sedang magang di perusahaannya,
     * karena tak ada kolom yang mengikat magang ke lowongan tertentu. Bila satu
     * perusahaan punya dua lowongan, keduanya menampilkan angka yang sama, dan
     * kalimat lama membuat itu tampak seperti kesalahan hitung.
     */
    public function ringkasanKuota(): ?string
    {
        if (! $this->punyaKuota()) {
            return null;
        }

        return $this->jumlahTerisi() . ' dari ' . $this->quota . ' mahasiswa di perusahaan ini';
    }

    /** Keterangan panjang untuk tooltip/penjelasan di halaman detail. */
    public function penjelasanKuota(): ?string
    {
        if (! $this->punyaKuota()) {
            return null;
        }

        return 'Kuota dicatat Kaprodi untuk ' . $this->company_display_name
            . ', dihitung dari mahasiswa yang sedang magang di sana — bukan per lowongan. '
            . 'Angka ini penanda, bukan pembatas: pengajuanmu tetap bisa dikirim.';
    }

    /**
     * Nama perusahaan untuk ditampilkan: dari Company terhubung (akun) atau free-text.
     */
    public function getCompanyDisplayNameAttribute(): string
    {
        return $this->company->name ?? $this->company_name ?? 'Perusahaan';
    }
}
