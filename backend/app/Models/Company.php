<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'user_id', 'name', 'address', 'field',
        'phone', 'email', 'verification_status', 'rejection_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function internships()
    {
        return $this->hasMany(Internship::class);
    }

    public function jobListings()
    {
        return $this->hasMany(JobListing::class);
    }
}
