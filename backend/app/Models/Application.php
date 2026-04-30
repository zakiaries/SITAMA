<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = ['student_id', 'job_listing_id', 'status'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }
}
