<?php

namespace Tests\Feature;

use App\Models\AssessmentComponent;
use App\Models\Internship;
use App\Models\StudentScore;
use Tests\FeatureTestCase;

/**
 * Angka nilai harus sama di ketiga portal yang menampilkannya.
 *
 * Portal dosen dulu memuat SELURUH AssessmentComponent tanpa menyaring
 * scorer_type, lalu merata-ratakan kesepuluhnya polos. Akibatnya dua komponen
 * dosen berbaur dengan delapan komponen industri dalam satu daftar tanpa
 * keterangan, bobot 20%/80% diabaikan, dan angka "Rata-rata" yang tercetak
 * bukan nilai siapa pun — bukan nilai dosen, bukan nilai industri, bukan nilai
 * akhir, dan tak ada padanannya di form resmi Polines.
 *
 * Bug ini lolos dari 499 pengujian karena tak satu pun menyentuh kartu nilai di
 * halaman detail dosen. Yang dijaga di sini karena itu bukan tampilannya,
 * melainkan KESERAGAMANNYA: ketiga portal wajib membaca dari nilaiSummary()
 * dan mencetak tiga angka yang sama.
 */
class NilaiSeragamAntarPortalTest extends FeatureTestCase
{
    /** Isi seluruh skor satu penilai. */
    private function isiSkor(Internship $internship, string $scorerType, callable $nilaiUntuk): void
    {
        foreach (AssessmentComponent::forScorer($scorerType)->with('detailedComponents')->get() as $komponen) {
            $nilai = $nilaiUntuk($komponen->name);

            foreach ($komponen->detailedComponents as $rinci) {
                StudentScore::create([
                    'internship_id'                    => $internship->id,
                    'detailed_assessment_component_id' => $rinci->id,
                    'scorer_type'                      => $scorerType,
                    'score'                            => $nilai,
                ]);
            }
        }
    }

    /**
     * Nilai sengaja dipilih supaya cara hitung yang BENAR dan yang SALAH
     * menghasilkan angka berbeda:
     *   berbobot (benar) → 0,2·8 + 0,8·9            = 8,8
     *   rata polos 10 komponen (salah) → (8+9+9·8)÷10 = 8,9
     * Kalau keduanya kebetulan sama, tesnya tak membuktikan apa pun.
     */
    private function siapkanNilai(): Internship
    {
        $internship = Internship::firstOrFail();

        $this->isiSkor($internship, 'lecturer', fn ($nama) => str_starts_with($nama, 'Proposal') ? 8 : 9);
        $this->isiSkor($internship, 'lecturer_industry', fn () => 9);

        return $internship->fresh();
    }

    public function test_portal_dosen_memisahkan_dua_penilai(): void
    {
        $student = $this->siapkanNilai()->student;

        $html = $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.mahasiswa.detail', $student))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Rata Dosen', $html,
            'Portal dosen tak memisahkan nilai dosen — dulu kesepuluh komponen '
            . 'ditumpuk jadi satu daftar tanpa keterangan penilainya.');
        $this->assertStringContainsString('Rata Industri', $html,
            'Portal dosen tak memisahkan nilai pembimbing industri.');
        $this->assertStringContainsString('Nilai Akhir (Dosen + Industri)', $html,
            'Portal dosen tak mencetak nilai akhir sebagaimana bunyi form resmi.');
    }

    public function test_portal_dosen_memakai_rata_berbobot_bukan_rata_polos(): void
    {
        $student = $this->siapkanNilai()->student;

        $html = $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.mahasiswa.detail', $student))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('8.8', $html,
            'Rata dosen harus berbobot (0,2·8 + 0,8·9 = 8,8).');
        $this->assertStringContainsString('17.8', $html,
            'Nilai akhir harus penjumlahan rata dosen + rata industri (8,8 + 9 = 17,8).');
        $this->assertStringNotContainsString('8.9', $html,
            'Muncul 8,9 — itu rata-rata polos atas sepuluh komponen kedua penilai, '
            . 'angka yang tak punya padanan di form resmi maupun di layar lain.');
    }

    /** Bobot per komponen ikut tercetak, seperti di form resmi. */
    public function test_portal_dosen_menampilkan_bobot_komponen(): void
    {
        $student = $this->siapkanNilai()->student;

        $html = $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.mahasiswa.detail', $student))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('(20%)', $html, 'Bobot Proposal 20% tak tercetak.');
        $this->assertStringContainsString('(80%)', $html, 'Bobot Laporan 80% tak tercetak.');
    }

    /**
     * Penjaga inti: tiga angka yang sama harus muncul di ketiga portal.
     * Kalau salah satu berubah cara hitungnya sendiri, tes ini jatuh.
     */
    public function test_ketiga_portal_mencetak_angka_yang_sama(): void
    {
        $internship = $this->siapkanNilai();
        $student    = $internship->student;
        $ringkasan  = $internship->nilaiSummary();

        $halaman = [
            'dosen'     => [$this->userByUsername('dosen1'),        route('dosen.mahasiswa.detail', $student)],
            'kaprodi'   => [$this->userByUsername('kaprodi'),       route('kaprodi.mahasiswa.detail', $student)],
            'mahasiswa' => [$this->userByUsername('3.34.23.2.01'),  route('mahasiswa.nilai')],
        ];

        foreach ($halaman as $peran => [$pengguna, $url]) {
            $html = $this->actingAs($pengguna)->get($url)->assertOk()->getContent();

            foreach ([
                'rata dosen'    => $ringkasan['lecturer']['average'],
                'rata industri' => $ringkasan['industry']['average'],
                'nilai akhir'   => $ringkasan['final'],
            ] as $sebutan => $angka) {
                $this->assertStringContainsString((string) $angka, $html,
                    "Portal {$peran} tak mencetak {$sebutan} ({$angka}) — ketiga portal "
                    . 'harus membaca angka yang sama dari nilaiSummary().');
            }
        }
    }
}
