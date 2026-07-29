<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailedAssessmentComponent extends Model
{
    use HasFactory;

    protected $table = 'detailed_assessment_components';

    protected $fillable = ['assessment_component_id', 'name', 'order'];

    public function assessmentComponent()
    {
        return $this->belongsTo(AssessmentComponent::class, 'assessment_component_id');
    }

    public function scores()
    {
        return $this->hasMany(StudentScore::class, 'detailed_assessment_component_id');
    }
}
