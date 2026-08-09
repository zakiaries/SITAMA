<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'the_class', 'study_program', 'major', 'academic_year', 'period_id',
        'status', 'status_note', 'status_changed_at', 'lecturer_id',
    ];

    protected $casts = [
        'status_changed_at' => 'datetime',
    ];

    /**
     * Mahasiswa sah tapi sedang berhenti — cuti, gap year, atau tersendat.
     *
     * Beda dari `rejected`: pendaftarannya diterima, hanya studinya berhenti
     * sementara, jadi ia bisa diaktifkan lagi tanpa mendaftar ulang.
     */
    const NONAKTIF = 'nonaktif';

    public function nonaktif(): bool
    {
        return $this->status === self::NONAKTIF;
    }

    /**
     * Program studi yang memakai SIMAMA — daftar tertutup, bukan teks bebas.
     *
     * Dulu diketik sendiri mahasiswa saat mendaftar, dan hasilnya satu prodi
     * tercatat dalam dua ejaan ("Teknik Rekayasa Komputer" vs "Teknologi
     * Rekayasa Komputer"), yang membuat pengelompokan per prodi bocor.
     *
     * Harus sama persis dengan nilai di ImporDosen::PRODI, karena dosen dan
     * mahasiswa dibandingkan lewat kolom ini tanpa penerjemahan.
     */
    const PRODI = [
        'Teknik Informatika',
        'Teknologi Rekayasa Komputer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Periode magang yang diikuti.
     *
     * Menggantikan peran `academic_year` sebagai penanda angkatan. Kolom lama
     * masih terisi dan masih dibaca beberapa tempat (dashboard dosen, ekspor
     * Excel, endpoint mobile), jadi keduanya berdampingan sampai pemakainya
     * dipindahkan.
     */
    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function internships()
    {
        return $this->hasMany(Internship::class);
    }

    public function guidances()
    {
        return $this->hasMany(Guidance::class);
    }

    /** Sesi seminar tempat mahasiswa ini menjadi penyaji. */
    public function seminars()
    {
        return $this->belongsToMany(Seminar::class, 'seminar_presenters');
    }

    /**
     * Seminar magangnya sudah disahkan dosen.
     *
     * Inilah titik tutup akademik yang sebenarnya — BUKAN berakhirnya magang.
     * Sesudah magangnya selesai, mahasiswa masih berkonsultasi menyiapkan
     * laporan dan seminarnya, jadi bimbingan harus tetap terbuka sampai
     * seminarnya disahkan.
     *
     * Perlu diketahui: pengesahan seminar tak bisa dibatalkan maupun dihapus,
     * sehingga kunci yang bertumpu padanya bersifat final — berbeda dari kunci
     * selesai-magang yang masih bisa dibuka kembali oleh Kaprodi.
     */
    public function seminarSelesai(): bool
    {
        return $this->seminars()->where('seminars.status', 'completed')->exists();
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
