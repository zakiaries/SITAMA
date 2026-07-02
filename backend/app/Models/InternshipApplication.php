<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipApplication extends Model
{
    protected $fillable = [
        'student_id', 'company_name', 'pic_name', 'pic_phone',
        'pic_email', 'position', 'start_date', 'proof_file',
        'status', 'rejection_reason',
    ];

    protected $casts = ['start_date' => 'date'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
