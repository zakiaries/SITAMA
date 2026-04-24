<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guidance extends Model
{
    protected $fillable = [
        'student_id',
        'title',
        'activity',
        'date',
        'name_file',
        'status',
        'lecturer_note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
