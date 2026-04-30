<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeminarRegistration extends Model
{
    protected $fillable = ['seminar_id', 'student_id', 'status'];

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
