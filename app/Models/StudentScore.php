<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentScore extends Model
{
    use HasFactory;

    protected $table = 'student_scores';

    protected $fillable = ['internship_id', 'detailed_assessment_component_id', 'score'];

    protected $casts = ['score' => 'float'];

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }

    public function detailedComponent()
    {
        return $this->belongsTo(DetailedAssessmentComponent::class, 'detailed_assessment_component_id');
    }
}
