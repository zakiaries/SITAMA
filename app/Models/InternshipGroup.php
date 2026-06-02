<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipGroup extends Model
{
    use HasFactory;

    protected $fillable = ['leader_application_id', 'job_listing_id'];

    public function leaderApplication()
    {
        return $this->belongsTo(Application::class, 'leader_application_id');
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }

    public function members()
    {
        return $this->hasMany(InternshipGroupMember::class, 'group_id');
    }
}
