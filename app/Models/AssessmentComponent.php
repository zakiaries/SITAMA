<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentComponent extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function detailedComponents()
    {
        return $this->hasMany(DetailedAssessmentComponent::class, 'assessment_component_id');
    }
}
