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
}
