<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'the_class', 'study_program', 'major', 'academic_year', 'status', 'lecturer_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function internships()
    {
        return $this->hasMany(Internship::class);
    }

    public function guidances()
    {
        return $this->hasMany(Guidance::class);
    }

    public function logBooks()
    {
        return $this->hasMany(LogBook::class);
    }

    public function activeInternship()
    {
        return $this->hasOne(Internship::class)->latest();
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }
}
