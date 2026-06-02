<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipGroupMember extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'student_id', 'status'];

    public function group()
    {
        return $this->belongsTo(InternshipGroup::class, 'group_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
