<?php

namespace Database\Seeders;

use App\Models\AssessmentComponent;
use App\Models\DetailedAssessmentComponent;
use Illuminate\Database\Seeder;

class AssessmentComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            'Kedisiplinan' => [
                'Kehadiran dan ketepatan waktu',
                'Kepatuhan terhadap peraturan perusahaan',
                'Tanggung jawab dalam menyelesaikan tugas',
            ],
            'Kemampuan Teknis' => [
                'Penguasaan bidang ilmu yang relevan',
                'Kemampuan menggunakan peralatan/teknologi',
                'Kualitas hasil kerja',
            ],
            'Kerjasama' => [
                'Kemampuan bekerja dalam tim',
                'Komunikasi dengan rekan kerja',
                'Kemampuan menerima arahan',
            ],
            'Inisiatif' => [
                'Kreativitas dalam menyelesaikan masalah',
                'Kemampuan bekerja mandiri',
                'Semangat belajar hal baru',
            ],
        ];

        foreach ($components as $componentName => $details) {
            $component = AssessmentComponent::create(['name' => $componentName]);

            foreach ($details as $detail) {
                DetailedAssessmentComponent::create([
                    'assessment_component_id' => $component->id,
                    'name'                    => $detail,
                ]);
            }
        }
    }
}
