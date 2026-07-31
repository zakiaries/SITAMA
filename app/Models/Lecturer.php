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

    /**
     * Rincian hal yang MENUNGGU TANGGAPAN dosen pembimbing kampus.
     * Dipakai untuk badge di sidebar: tanpa ini dosen tak punya cara tahu
     * ada unggahan baru selain membuka satu per satu mahasiswanya.
     *
     * @return array{logbook:int, bimbingan:int, laporan:int, total:int}
     */
    public function menungguTanggapanKampus(): array
    {
        $studentIds = $this->internships()->pluck('student_id')->unique();

        if ($studentIds->isEmpty()) {
            return ['logbook' => 0, 'bimbingan' => 0, 'laporan' => 0, 'total' => 0];
        }

        $rincian = [
            'logbook'   => LogBook::whereIn('student_id', $studentIds)->whereNull('lecturer_note')->count(),
            'bimbingan' => Guidance::whereIn('student_id', $studentIds)->where('status', 'pending')->count(),
            'laporan'   => InternshipReport::whereIn('student_id', $studentIds)->where('status', 'pending')->count(),
        ];

        return $rincian + ['total' => array_sum($rincian)];
    }

    /** Logbook mahasiswa asuhan yang belum dikomentari pembimbing industri. */
    public function menungguTanggapanIndustri(): int
    {
        $studentIds = $this->industryInternships()->pluck('student_id')->unique();

        if ($studentIds->isEmpty()) {
            return 0;
        }

        return LogBook::whereIn('student_id', $studentIds)->whereNull('industry_note')->count();
    }
}
