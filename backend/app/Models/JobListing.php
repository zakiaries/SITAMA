<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    protected $fillable = [
        'company_id', 'title', 'division', 'description',
        'skills', 'location', 'job_type', 'quota',
        'duration_months', 'pic_email', 'status',
    ];

    protected $casts = [
        'skills' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
