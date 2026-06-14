<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'company_name', 'title', 'division', 'description', 'skills',
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
