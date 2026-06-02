<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'job_listing_id', 'status', 'type'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }

    public function internshipGroup()
    {
        return $this->hasOne(InternshipGroup::class, 'leader_application_id');
    }
}
