<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'company_id',
        'company_name', 'company_address', 'company_field', 'company_phone', 'company_email',
        'proof_file', 'position', 'bidang', 'start_date',
        'pic_name', 'pic_email', 'pic_phone',
        'status', 'rejection_reason',
        'created_company_id', 'created_lecturer_id',
    ];

    protected $casts = ['start_date' => 'date'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdCompany()
    {
        return $this->belongsTo(Company::class, 'created_company_id');
    }

    public function createdLecturer()
    {
        return $this->belongsTo(Lecturer::class, 'created_lecturer_id');
    }
}
