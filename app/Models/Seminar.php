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
        'lecturer_id', 'title', 'program', 'date', 'time', 'location', 'organizer',
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
