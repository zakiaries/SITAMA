<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'the_class',
        'study_program',
        'major',
        'academic_year',
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
}
