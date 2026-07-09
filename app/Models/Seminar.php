<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    use HasFactory;

    /** Jumlah minimal tamu (audiens) yang mengisi berita acara. */
    public const MIN_GUESTS = 10;

    /** Legacy: dipakai API mobile (kuota audiens mahasiswa). Dipertahankan agar mobile tidak rusak. */
    public const MIN_AUDIENCE = 10;

    protected $fillable = [
        'title', 'program', 'date', 'time', 'location', 'organizer',
        'description', 'qr_code', 'status', 'student_id',
        'rejection_reason', 'access_token',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function registrations()
    {
        return $this->hasMany(SeminarRegistration::class);
    }

    public function attendances()
    {
        return $this->hasMany(SeminarAttendance::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Jumlah tamu yang sudah mengisi berita acara.
     */
    public function guestCount(): int
    {
        return $this->attendances->count();
    }

    public function guestMet(): bool
    {
        return $this->guestCount() >= self::MIN_GUESTS;
    }
}
