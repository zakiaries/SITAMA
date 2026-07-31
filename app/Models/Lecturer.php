<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Magang yang dibimbing sebagai dosen pembimbing KAMPUS (internships.lecturer_id). */
    public function internships()
    {
        return $this->hasMany(Internship::class);
    }

    /**
     * Magang yang dibimbing sebagai PEMBIMBING INDUSTRI
     * (internships.lecturer_industry_id) — kolomnya beda dari relasi di atas,
     * jadi harus relasi tersendiri. Menumpuk kondisi industri di atas
     * internships() menghasilkan query yang menuntut satu dosen menjadi
     * pembimbing kampus sekaligus industri → hasilnya selalu nol.
     */
    public function industryInternships()
    {
        return $this->hasMany(Internship::class, 'lecturer_industry_id');
    }

    /** Mahasiswa yang diplot Kaprodi ke dosen ini (students.lecturer_id). */
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
