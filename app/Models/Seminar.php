<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Sesi seminar hasil magang.
 *
 * Model baru: 1 sesi dimiliki 1 dosen pembimbing dan berisi banyak mahasiswa
 * penyaji (relasi seminar_presenters). Alur: dosen buat sesi (draft) → mahasiswa
 * isi ketersediaan → dosen finalkan jadwal (scheduled) → audiens absen via login
 * (min. MIN_GUESTS) → dosen sahkan (completed).
 */
class Seminar extends Model
{
    use HasFactory;

    /** Jumlah minimal audiens (login) yang mengisi daftar hadir per sesi. */
    public const MIN_GUESTS = 15;

    /** Legacy: dipakai API mobile lama. Dipertahankan agar tidak error saat referensi. */
    public const MIN_AUDIENCE = 15;

    protected $fillable = [
        'lecturer_id', 'title', 'program', 'period_id', 'date', 'time', 'location', 'organizer',
        'description', 'qr_code', 'status', 'student_id',
        'rejection_reason', 'access_token', 'witnessed_at',
    ];

    protected $casts = [
        'date'         => 'date',
        'witnessed_at' => 'datetime',
    ];

    /** Dosen pembimbing pemilik/penyaksi sesi. */
    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    /** Periode magang yang diseminarkan. */
    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * Semester & tahun akademik untuk kepala berita acara.
     *
     * Diambil dari PERIODE MAGANG-nya, bukan dari tanggal seminar. Seminar
     * berlangsung sesudah magang dan kadang jatuh di semester berikutnya:
     * mahasiswa yang magang pada Genap 2025/2026 lalu seminarnya Agustus 2026
     * akan menerima dokumen bertuliskan "Gasal 2026/2027" bila tanggal seminar
     * yang dijadikan acuan — periode yang bukan miliknya, di berkas yang
     * ditandatangani.
     *
     * Sesi lama yang periodenya tak diketahui jatuh kembali ke tanggal seminar,
     * karena dokumen dengan tahun akademik yang mendekati tetap lebih berguna
     * daripada dokumen dengan bagian yang kosong.
     *
     * @return array{semester: string, tahun_akademik: string}
     */
    public function periodeDokumen(): array
    {
        if ($this->period) {
            return [
                'semester'       => Period::SEMESTER[$this->period->semester] ?? $this->period->semester,
                'tahun_akademik' => $this->period->academic_year,
            ];
        }

        [$tahun, $semester] = Period::dariTanggal($this->date ?? now());

        return [
            'semester'       => Period::SEMESTER[$semester] ?? $semester,
            'tahun_akademik' => $tahun,
        ];
    }

    /** Mahasiswa penyaji dalam sesi ini (beserta ketersediaan tanggalnya). */
    public function presenters()
    {
        return $this->hasMany(SeminarPresenter::class);
    }

    /** Data mahasiswa penyaji (shortcut lewat pivot). */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'seminar_presenters');
    }

    public function attendances()
    {
        return $this->hasMany(SeminarAttendance::class);
    }

    /** Legacy (per-mahasiswa) — dipertahankan untuk kompatibilitas data lama. */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function registrations()
    {
        return $this->hasMany(SeminarRegistration::class);
    }

    public function guestCount(): int
    {
        return $this->attendances->count();
    }

    public function guestMet(): bool
    {
        return $this->guestCount() >= self::MIN_GUESTS;
    }

    /**
     * Tanggal sesi sudah terlewat (sebelum hari ini).
     *
     * Sesudah hari-H, seminarnya sudah berlangsung: mengubah jam, ruang, atau
     * judul hanya akan membuat catatan tidak cocok dengan yang benar-benar
     * terjadi — dan penyaji/audiens ikut menerima notifikasi perubahan yang
     * menyesatkan. Yang masih boleh dilakukan dosen hanyalah mengesahkan.
     */
    public function jadwalSudahLewat(): bool
    {
        return $this->date !== null && $this->date->lt(today());
    }

    /** Toleransi datang lebih awal & sesi molor, dalam menit. */
    public const HADIR_TOLERANSI_AWAL  = 30;
    public const HADIR_TOLERANSI_AKHIR = 60;

    /**
     * Daftar hadir (QR) masih boleh diisi.
     *
     * Dulu terbuka sejak DETIK dosen menetapkan jadwal — audiens bisa mengisi
     * daftar hadir berminggu-minggu sebelum seminarnya berlangsung, yang
     * membuat syarat minimal audiens kehilangan artinya: ia mestinya bukti
     * orang benar-benar datang, bukan bukti orang pernah membuka tautan.
     *
     * Kini digerbangi hari-H, dan bila jamnya bisa dibaca, jam itu juga.
     */
    public function daftarHadirTerbuka(): bool
    {
        if ($this->status !== 'scheduled' || $this->date === null) {
            return false;
        }

        if (! $this->date->isSameDay(today())) {
            return false;
        }

        $jendela = $this->jendelaJam();

        return $jendela === null || now()->between($jendela[0], $jendela[1]);
    }

    /**
     * Rentang jam daftar hadir, dibaca dari kolom `time`.
     *
     * Kolomnya teks bebas ("09.00 - 11.00 WIB", "09:00", "pagi"), jadi jamnya
     * DIBACA sebisanya, bukan dituntut. Kalau tak terbaca, kembalikan null dan
     * biarkan hari-H saja yang menggerbangi — mengunci audiens di tengah
     * seminar yang sedang berlangsung karena dosennya menulis "pagi" jauh lebih
     * merugikan daripada membiarkan absensi terbuka sehari penuh.
     *
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon}|null
     */
    public function jendelaJam(): ?array
    {
        if (! preg_match_all('/(\d{1,2})[.:](\d{2})/', (string) $this->time, $cocok, PREG_SET_ORDER)) {
            return null;
        }

        $jam = fn (array $m) => $this->date->copy()
            ->setTime(min((int) $m[1], 23), min((int) $m[2], 59));

        $mulai = $jam($cocok[0])->subMinutes(self::HADIR_TOLERANSI_AWAL);

        $selesai = isset($cocok[1])
            ? $jam($cocok[1])->addMinutes(self::HADIR_TOLERANSI_AKHIR)
            : $jam($cocok[0])->addHours(4);

        // Jam selesai lebih kecil dari mulai (mis. salah ketik) — jangan
        // menghasilkan rentang kosong yang mengunci semua orang.
        return $selesai->lte($mulai) ? null : [$mulai, $selesai];
    }

    /** Kenapa daftar hadir tertutup — untuk pesan di layar, bukan sekadar 404. */
    public function alasanHadirTertutup(): ?string
    {
        if ($this->daftarHadirTerbuka()) {
            return null;
        }

        if ($this->status !== 'scheduled') {
            return 'Daftar hadir hanya dibuka untuk seminar yang sudah dijadwalkan.';
        }

        if ($this->date === null) {
            return 'Jadwal seminar ini belum ditetapkan dosen pembimbing.';
        }

        if ($this->date->gt(today())) {
            return 'Daftar hadir baru dibuka pada hari seminar, ' . $this->date->translatedFormat('d F Y') . '.';
        }

        if ($this->date->lt(today())) {
            return 'Seminar ini sudah berlangsung pada ' . $this->date->translatedFormat('d F Y') . '.';
        }

        return 'Daftar hadir hanya dibuka pada jam seminar' . ($this->time ? " ({$this->time})" : '') . '.';
    }

    /** Interval rotasi QR daftar hadir (detik). */
    public const QR_INTERVAL = 20;

    /**
     * Token QR daftar hadir berbasis waktu (HMAC dg APP_KEY), berganti tiap
     * QR_INTERVAL detik. Tidak perlu disimpan — bisa dihitung ulang & diverifikasi.
     */
    public function rotatingToken(?int $window = null): string
    {
        $window = $window ?? intdiv(time(), self::QR_INTERVAL);

        return substr(hash_hmac('sha256', "seminar-hadir:{$this->id}:{$window}", (string) config('app.key')), 0, 16);
    }

    /** Valid bila cocok window sekarang atau sebelumnya (toleransi ~1 interval). */
    public function isValidRotatingToken(?string $rt): bool
    {
        if (! $rt) {
            return false;
        }

        $w = intdiv(time(), self::QR_INTERVAL);

        return hash_equals($this->rotatingToken($w), $rt)
            || hash_equals($this->rotatingToken($w - 1), $rt);
    }
}
