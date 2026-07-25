<?php

namespace Database\Seeders;

use App\Models\AssessmentComponent;
use App\Models\DetailedAssessmentComponent;
use Illuminate\Database\Seeder;

class RubrikPenilaianSeeder extends Seeder
{
    /**
     * Rubrik penilaian magang: 4 komponen utama + 12 komponen rinci.
     * Data referensi wajib — halaman input nilai bergantung padanya.
     * Idempoten: aman dijalankan berulang (firstOrCreate).
     */
    public function run(): void
    {
        $rubrik = [
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

        foreach ($rubrik as $namaKomponen => $rincian) {
            $component = AssessmentComponent::firstOrCreate(['name' => $namaKomponen]);

            foreach ($rincian as $namaRinci) {
                DetailedAssessmentComponent::firstOrCreate([
                    'assessment_component_id' => $component->id,
                    'name'                    => $namaRinci,
                ]);
            }
        }
    }
}
