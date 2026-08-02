<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'the_class', 'study_program', 'major', 'academic_year', 'status', 'lecturer_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function internships()
    {
        return $this->hasMany(Internship::class);
    }

    public function guidances()
    {
        return $this->hasMany(Guidance::class);
    }

    public function logBooks()
    {
        return $this->hasMany(LogBook::class);
    }

    public function report()
    {
        return $this->hasOne(InternshipReport::class)->latestOfMany();
    }

    public function activeInternship()
    {
        return $this->hasOne(Internship::class)->latest();
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    /**
     * Mahasiswa yang dibimbing seorang dosen pembimbing kampus.
     *
     * Ada DUA sumber, dan dulu tiap sisi hanya membaca salah satunya sehingga
     * tak pernah cocok: mahasiswa melihat dospem dari students.lecturer_id
     * (hasil plot Kaprodi), sedangkan portal dosen menyaring lewat
     * internships.lecturer_id. Akibatnya mahasiswa yang sudah diplot tapi
     * magangnya belum terbentuk tak terlihat oleh dosennya — padahal ia sudah
     * boleh mengajukan bimbingan, dan bimbingannya jadi tak pernah sampai.
     *
     * Plot bisa berpindah, sementara magang menyimpan dosen saat itu, jadi
     * keduanya tetap dipakai — bukan salah satu.
     */
    public function scopeDibimbingOleh($query, int $lecturerId)
    {
        return $query->where(function ($q) use ($lecturerId) {
            $q->where('lecturer_id', $lecturerId)
              ->orWhereHas('internships', fn ($i) => $i->where('lecturer_id', $lecturerId));
        });
    }
}
