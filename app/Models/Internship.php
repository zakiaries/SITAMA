<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'lecturer_id', 'company_id', 'lecturer_industry_id',
        'position', 'start_date', 'end_date', 'is_finished', 'finish_requested',
        'performance_notes', 'performance_notes_by', 'performance_notes_date',
        'certificate_path',
    ];

    const MIN_LOGBOOK = 20;

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'is_finished'      => 'boolean',
        'finish_requested' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function lecturerIndustry()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_industry_id');
    }

    public function scores()
    {
        return $this->hasMany(StudentScore::class);
    }

    /**
     * Rata-rata nilai per komponen penilaian (gabungan dosen kampus & dosen industri),
     * beserta rata-rata keseluruhan.
     */
    public function nilaiSummary(): array
    {
        $components = AssessmentComponent::with(['detailedComponents.scores' => function ($q) {
            $q->where('internship_id', $this->id);
        }])->get();

        $items = $components->map(function ($comp) {
            $scores = $comp->detailedComponents->flatMap->scores->pluck('score')->filter();
            return [
                'name' => $comp->name,
                'avg'  => $scores->count() > 0 ? round($scores->avg(), 2) : null,
            ];
        });

        $filled = $items->pluck('avg')->filter();

        return [
            'items'   => $items,
            'overall' => $filled->count() > 0 ? round($filled->avg(), 2) : null,
        ];
    }
}
