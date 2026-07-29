<?php

namespace Database\Seeders;

use App\Models\AssessmentComponent;
use App\Models\DetailedAssessmentComponent;
use Illuminate\Database\Seeder;

class RubrikPenilaianSeeder extends Seeder
{
    /**
     * Rubrik penilaian magang — RUBRIK TERPISAH per penilai, sesuai form resmi:
     *
     *  • Dosen pembimbing (berbobot): Proposal 20% + Laporan 80% (skala 1–10).
     *  • Pembimbing industri: 8 komponen, dinilai per komponen, rata = Total ÷ 8.
     *
     * Idempoten: bila rubrik baru sudah ada (ada komponen scorer_type industri),
     * seeder dilewati agar TIDAK menghapus skor yang sudah tersimpan.
     */
    public function run(): void
    {
        if (AssessmentComponent::where('scorer_type', 'lecturer_industry')->exists()) {
            return; // sudah pakai rubrik baru — jangan reset.
        }

        // Reset rubrik lama (FK cascade ikut menghapus detail + skor lama).
        DetailedAssessmentComponent::query()->delete();
        AssessmentComponent::query()->delete();

        // ── Dosen pembimbing (berbobot) ──
        $this->component('lecturer', 'Proposal', 20, 1, [
            'Tujuan dan sasaran Magang',
            'Kesesuaian antara tujuan dan sasaran',
            'Kesesuaian perencanaan kerja',
            'Sistematika penulisan',
        ]);
        $this->component('lecturer', 'Laporan', 80, 2, [
            'Sistematika penulisan',
            'Bahasa: mudah dan dimengerti',
            'Bahasa: Bahasa Indonesia sesuai EYD',
            'Isi: kualitas aktivitas mahasiswa',
            'Isi: pengalaman baru yang diperoleh',
            'Isi: kemampuan memecahkan masalah',
            'Isi: kemampuan menyimpulkan',
            'Isi: kelengkapan lampiran',
        ]);

        // ── Pembimbing industri (8 komponen, per komponen, tanpa bobot khusus) ──
        $industri = [
            ['Kemampuan Beradaptasi dengan Lingkungan', 'Penyesuaian diri dengan lingkungan kerja'],
            ['Keterampilan dalam Menjalankan Tugas', 'Kesesuaian instruksi, kualitas hasil, ketepatan waktu, pemecahan masalah'],
            ['Tanggung Jawab Terhadap Tugas', 'Tanggung jawab dalam menyelesaikan tugas'],
            ['Inisiatif dan Kreativitas', 'Inisiatif dan kreativitas dalam bekerja'],
            ['Komunikasi', 'Kerja sama tim serta hubungan dengan atasan, rekan, dan relasi'],
            ['Kedisiplinan', 'Kedisiplinan dalam bekerja'],
            ['Kemandirian', 'Kemampuan bekerja secara mandiri'],
            ['Sikap Potensial', 'Sikap kerja, disiplin, loyalitas, motivasi, dan penampilan'],
        ];
        foreach ($industri as $i => [$nama, $detail]) {
            $this->component('lecturer_industry', $nama, null, $i + 1, [$detail]);
        }
    }

    /** Buat 1 komponen + rincian-rinciannya. */
    private function component(string $scorerType, string $name, ?float $weight, int $order, array $details): void
    {
        $component = AssessmentComponent::create([
            'name'        => $name,
            'scorer_type' => $scorerType,
            'weight'      => $weight,
            'order'       => $order,
        ]);

        foreach ($details as $j => $detail) {
            DetailedAssessmentComponent::create([
                'assessment_component_id' => $component->id,
                'name'                    => $detail,
                'order'                   => $j + 1,
            ]);
        }
    }
}
