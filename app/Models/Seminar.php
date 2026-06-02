<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    use HasFactory;

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
}
