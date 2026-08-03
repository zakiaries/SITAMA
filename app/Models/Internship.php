<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'lecturer_id', 'company_id', 'lecturer_industry_id',
        'position', 'start_date', 'end_date', 'is_finished', 'finish_requested',
        'performance_notes', 'performance_notes_by', 'performance_notes_date',
        'certificate_path',
    ];

    const MIN_LOGBOOK = 20;

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'is_finished'      => 'boolean',
        'finish_requested' => 'boolean',
    ];

    /**
     * Isian mahasiswa (sertifikat, logbook, bimbingan) sudah tidak boleh diubah.
     *
     * Berlaku sejak pengajuan selesai DIKIRIM, bukan hanya setelah di-ACC:
     * pengajuan itu digerbangi checklist kelengkapan, jadi mengubah isinya
     * sesudah dikirim membuat yang diperiksa Kaprodi berbeda dari yang diajukan.
     *
     * Selalu ada jalan keluar: selama belum di-ACC mahasiswa bisa membatalkan
     * pengajuannya sendiri, dan setelah di-ACC Kaprodi bisa membuka kembali.
     */
    public function terkunciUntukMahasiswa(): bool
    {
        return $this->is_finished || $this->finish_requested;
    }

    /**
     * Kelengkapan mahasiswa yang harus ada sebelum pembimbing boleh menilai.
     *
     * Penilaian menilai keseluruhan magang, jadi tak masuk akal diisi saat
     * mahasiswanya baru mengisi tiga logbook dan laporannya belum ada.
     *
     * Sertifikat SENGAJA tidak jadi syarat, meski ia bagian dari checklist
     * selesai magang: sebagian perusahaan baru menerbitkannya jauh setelah
     * magang berakhir, dan menahan penilaian karena itu menghukum mahasiswa atas
     * hal yang bukan kendalinya.
     *
     * Nilai kedua pembimbing juga tidak ikut — justru itulah yang sedang
     * digerbangi. Checklist selesai magang menuntut syarat di sini DITAMBAH
     * sertifikat dan kedua nilai, sehingga urutannya jalan satu arah: mahasiswa
     * melengkapi → pembimbing menilai → mahasiswa mengajukan selesai → Kaprodi
     * meng-ACC. Menggerbangi nilai dengan is_finished akan membuat keduanya
     * saling menunggu dan tak ada yang bisa bergerak.
     */
    public function syaratPenilaian(): array
    {
        $student = $this->student;

        return [
            'laporan' => $student?->report?->status === 'approved',
            'logbook' => ($student?->logBooks()->count() ?? 0) >= self::MIN_LOGBOOK,
        ];
    }

    public function siapDinilai(): bool
    {
        return ! in_array(false, $this->syaratPenilaian(), true);
    }

    /**
     * Kenapa nilai belum bisa diisi — disusun untuk dibaca PEMBIMBING, bukan
     * mahasiswa, sehingga menyebut apa yang perlu diselesaikan mahasiswanya.
     */
    public function alasanBelumSiapDinilai(): ?string
    {
        if ($this->siapDinilai()) {
            return null;
        }

        $syarat  = $this->syaratPenilaian();
        $logbook = $this->student?->logBooks()->count() ?? 0;

        $kurang = array_filter([
            $syarat['logbook'] ? null : "logbook belum lengkap (baru {$logbook} dari " . self::MIN_LOGBOOK . ')',
            $syarat['laporan'] ? null : 'laporan akhir belum di-ACC dosen pembimbing',
        ]);

        return 'Penilaian belum bisa diisi karena mahasiswa ini belum menyelesaikan magangnya: '
            . implode(', ', $kurang) . '.';
    }

    /** Alasan terkunci, untuk pesan galat maupun keterangan di halaman. */
    public function alasanTerkunci(): ?string
    {
        if ($this->is_finished) {
            return 'Magang kamu sudah ditandai selesai oleh Kaprodi, jadi datanya terkunci. '
                . 'Hubungi Kaprodi bila ada yang perlu diperbaiki.';
        }

        if ($this->finish_requested) {
            return 'Pengajuan selesai magang sedang menunggu ACC Kaprodi, jadi datanya terkunci. '
                . 'Batalkan pengajuan lebih dulu bila masih ada yang perlu diperbaiki.';
        }

        return null;
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function lecturerIndustry()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_industry_id');
    }

    public function scores()
    {
        return $this->hasMany(StudentScore::class);
    }

    /**
     * Rata-rata nilai per komponen penilaian (gabungan dosen kampus & dosen industri),
     * beserta rata-rata keseluruhan.
     */
    /**
     * Ringkasan nilai magang sesuai form resmi:
     *  • lecturer (dosen)  : berbobot — Proposal 20% + Laporan 80%.
     *  • industry          : rata polos 8 komponen (Total ÷ 8).
     *  • final             : rata dosen + rata industri (DIJUMLAH), diserahkan ke
     *                        kaprodi. Null bila salah satu penilai belum menilai.
     * Skala 1–10.
     */
    public function nilaiSummary(): array
    {
        $lecturer = $this->scorerSummary('lecturer', true);
        $industry = $this->scorerSummary('lecturer_industry', false);

        $final = ($lecturer['average'] !== null && $industry['average'] !== null)
            ? round($lecturer['average'] + $industry['average'], 2)
            : null;

        return [
            'lecturer' => $lecturer,
            'industry' => $industry,
            'final'    => $final,
        ];
    }

    /**
     * Rangkum nilai satu penilai.
     * @param  bool  $weighted  true = rata berbobot (pakai kolom weight komponen).
     */
    private function scorerSummary(string $scorerType, bool $weighted): array
    {
        $components = AssessmentComponent::forScorer($scorerType)
            ->with(['detailedComponents.scores' => fn ($q) => $q
                ->where('internship_id', $this->id)
                ->where('scorer_type', $scorerType)])
            ->get();

        $comps = $components->map(function ($comp) {
            $scores = $comp->detailedComponents->flatMap->scores->pluck('score')
                ->filter(fn ($v) => $v !== null);
            return [
                'name'   => $comp->name,
                'weight' => $comp->weight !== null ? (float) $comp->weight : null,
                'avg'    => $scores->count() > 0 ? round($scores->avg(), 2) : null,
            ];
        });

        $rated = $comps->filter(fn ($c) => $c['avg'] !== null);

        if ($rated->isEmpty()) {
            $average = null;
        } elseif ($weighted && $rated->every(fn ($c) => $c['weight'] !== null)) {
            // Berbobot; dinormalisasi atas komponen yang sudah dinilai.
            $totalWeight = $rated->sum('weight');
            $average = $totalWeight > 0
                ? round($rated->sum(fn ($c) => $c['avg'] * $c['weight']) / $totalWeight, 2)
                : null;
        } else {
            $average = round($rated->avg('avg'), 2);
        }

        return ['average' => $average, 'components' => $comps->values()->all()];
    }
}
