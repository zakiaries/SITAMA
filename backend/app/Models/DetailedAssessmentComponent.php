<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailedAssessmentComponent extends Model
{
    protected $fillable = ['assessment_component_id', 'name'];

    public function component()
    {
        return $this->belongsTo(AssessmentComponent::class, 'assessment_component_id');
    }

    public function scores()
    {
        return $this->hasMany(StudentScore::class, 'detailed_assessment_component_id');
    }
}
