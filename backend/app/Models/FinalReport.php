<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalReport extends Model
{
    protected $fillable = ['student_id', 'title', 'file_path', 'status', 'lecturer_note'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
