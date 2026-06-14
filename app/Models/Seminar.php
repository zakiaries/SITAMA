<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    use HasFactory;

    /** Jumlah minimal audiens (adik tingkat) untuk seminar hasil magang. */
    public const MIN_AUDIENCE = 10;

    protected $fillable = [
        'title', 'program', 'date', 'time', 'location', 'organizer',
        'description', 'qr_code', 'status', 'student_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function registrations()
    {
        return $this->hasMany(SeminarRegistration::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Jumlah audiens = peserta terdaftar selain mahasiswa penyaji itu sendiri.
     */
    public function audienceCount(): int
    {
        return $this->registrations
            ->where('student_id', '!=', $this->student_id)
            ->count();
    }

    public function audienceMet(): bool
    {
        return $this->audienceCount() >= self::MIN_AUDIENCE;
    }
}
