<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'study_program'];

    /**
     * Program studi yang dikenal, beserta labelnya di layar.
     *
     * Kuncinya nilai yang disimpan — disamakan dengan students.study_program
     * agar dosen dan mahasiswa bisa dibandingkan tanpa penerjemahan.
     */
    public const PRODI = [
        'Teknik Informatika'          => 'Teknik Informatika (D3)',
        'Teknologi Rekayasa Komputer' => 'Teknologi Rekayasa Komputer (D4)',
    ];

    /** Label untuk ditampilkan; yang kosong jujur disebut belum diisi. */
    public function labelProdi(): string
    {
        return self::PRODI[$this->study_program] ?? 'Belum diisi';
    }

    /** Saring menurut prodi; 'kosong' menjaring yang belum diisi. */
    public function scopeProdi($query, ?string $prodi)
    {
        if ($prodi === null || $prodi === '' || $prodi === 'semua') {
            return $query;
        }

        return $prodi === 'kosong'
            ? $query->whereNull('study_program')
            : $query->where('study_program', $prodi);
    }

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
