<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    protected $fillable = [
        'student_id',
        'lecturer_id',
        'company_id',
        'lecturer_industry_id',
        'position',
        'start_date',
        'end_date',
        'is_finished',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'is_finished' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function lecturerIndustry()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_industry_id');
    }

    public function scores()
    {
        return $this->hasMany(StudentScore::class);
    }
}
