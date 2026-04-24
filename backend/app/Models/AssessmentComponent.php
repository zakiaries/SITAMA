<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentComponent extends Model
{
    protected $fillable = ['name'];

    public function detailedComponents()
    {
        return $this->hasMany(DetailedAssessmentComponent::class);
    }
}
