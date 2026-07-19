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
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Nama perusahaan untuk ditampilkan: dari Company terhubung (akun) atau free-text.
     */
    public function getCompanyDisplayNameAttribute(): string
    {
        return $this->company->name ?? $this->company_name ?? 'Perusahaan';
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
