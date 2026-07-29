<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentComponent extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'scorer_type', 'weight', 'order'];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function detailedComponents()
    {
        return $this->hasMany(DetailedAssessmentComponent::class, 'assessment_component_id')->orderBy('order');
    }

    /** Rubrik milik satu penilai: 'lecturer' (dosen) atau 'lecturer_industry'. */
    public function scopeForScorer($query, string $scorerType)
    {
        return $query->where('scorer_type', $scorerType)->orderBy('order');
    }
}
