<?php

namespace Tests\Feature;

use App\Models\AssessmentComponent;
use App\Models\DetailedAssessmentComponent;
use App\Models\Internship;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\FeatureTestCase;

class NilaiTest extends FeatureTestCase
{
    /** Isi seluruh skor satu penilai untuk internship. */
    private function scoreAll(Internship $internship, string $scorerType, callable $valueFor): void
    {
        foreach (AssessmentComponent::forScorer($scorerType)->with('detailedComponents')->get() as $component) {
            $value = $valueFor($component->name);
            foreach ($component->detailedComponents as $detail) {
                StudentScore::create([
                    'internship_id'                    => $internship->id,
                    'detailed_assessment_component_id' => $detail->id,
                    'scorer_type'                      => $scorerType,
                    'score'                            => $value,
                ]);
            }
        }
    }

    public function test_rubrik_terpisah_per_penilai(): void
    {
        $this->assertSame(2, AssessmentComponent::forScorer('lecturer')->count());
        $this->assertSame(8, AssessmentComponent::forScorer('lecturer_industry')->count());

        // Bobot dosen: Proposal 20 + Laporan 80.
        $bobot = AssessmentComponent::forScorer('lecturer')->pluck('weight', 'name');
        $this->assertEquals(20, (int) $bobot['Proposal']);
        $this->assertEquals(80, (int) $bobot['Laporan']);
    }

    public function test_nilai_dosen_berbobot_industri_rata_akhir_dijumlah(): void
    {
        $internship = Internship::firstOrFail();

        // Dosen: Proposal = 8, Laporan = 9  → 0,2·8 + 0,8·9 = 8,8
        $this->scoreAll($internship, 'lecturer', fn ($name) => str_starts_with($name, 'Proposal') ? 8 : 9);
        // Industri: semua 9 → rata 9
        $this->scoreAll($internship, 'lecturer_industry', fn () => 9);

        $summary = $internship->fresh()->nilaiSummary();

        $this->assertEqualsWithDelta(8.8, $summary['lecturer']['average'], 0.001);
        $this->assertEqualsWithDelta(9.0, $summary['industry']['average'], 0.001);
        $this->assertEqualsWithDelta(17.8, $summary['final'], 0.001); // dijumlah
        $this->assertCount(2, $summary['lecturer']['components']);
        $this->assertCount(8, $summary['industry']['components']);
    }

    public function test_nilai_akhir_null_bila_salah_satu_penilai_belum_menilai(): void
    {
        $internship = Internship::firstOrFail();
        $this->scoreAll($internship, 'lecturer', fn () => 8);

        $summary = $internship->fresh()->nilaiSummary();
        $this->assertNotNull($summary['lecturer']['average']);
        $this->assertNull($summary['industry']['average']);
        $this->assertNull($summary['final']); // butuh dua-duanya
    }

    public function test_api_dosen_menolak_skor_di_luar_1_sampai_10(): void
    {
        Sanctum::actingAs($this->userByUsername('dosen1'));
        $student = Student::whereHas('user', fn ($q) => $q->where('username', '3.34.23.2.01'))->firstOrFail();
        $detail  = DetailedAssessmentComponent::whereHas('assessmentComponent',
            fn ($q) => $q->where('scorer_type', 'lecturer'))->firstOrFail();

        $this->postJson("/api/dosen/mahasiswa/{$student->id}/nilai", ['scores' => [$detail->id => 15]])
            ->assertStatus(422);

        $this->postJson("/api/dosen/mahasiswa/{$student->id}/nilai", ['scores' => [$detail->id => 9]])
            ->assertStatus(200);

        $this->assertDatabaseHas('student_scores', [
            'internship_id'                    => Internship::firstOrFail()->id,
            'detailed_assessment_component_id' => $detail->id,
            'scorer_type'                      => 'lecturer',
            'score'                            => 9,
        ]);
    }
}
