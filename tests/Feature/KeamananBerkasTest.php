<?php

namespace Tests\Feature;

use App\Models\CompanyRequest;
use App\Models\Guidance;
use App\Models\InternshipReport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\FeatureTestCase;

/**
 * Dokumen mahasiswa dulu disimpan di disk `public` yang dilayani langsung web
 * server — Laravel tak pernah dilibatkan, jadi siapa pun yang tahu URL-nya bisa
 * mengunduh laporan akhir, sertifikat, lampiran bimbingan, dan bukti penerimaan
 * magang tanpa login sama sekali.
 *
 * Sekarang semuanya lewat BerkasController: disk privat + pemeriksaan hak akses.
 */
class KeamananBerkasTest extends FeatureTestCase
{
    private function bimbinganBerkas(): Guidance
    {
        Storage::fake('local');

        $student = $this->userByUsername('3.34.23.2.01')->student;
        $path    = UploadedFile::fake()->create('draft.pdf', 20, 'application/pdf')
            ->store('guidances', 'local');

        return Guidance::create([
            'student_id' => $student->id, 'title' => 'B1', 'activity' => 'a',
            'date' => '2024-07-01', 'status' => 'pending', 'name_file' => $path,
        ]);
    }

    // ── Yang berhak ─────────────────────────────────────────────────────────

    public function test_pemilik_bisa_membuka_berkasnya(): void
    {
        $g = $this->bimbinganBerkas();

        $this->actingAs($this->userByUsername('3.34.23.2.01'))
            ->get(route('berkas.bimbingan', $g))->assertOk();
    }

    public function test_dosen_pembimbingnya_bisa_membuka(): void
    {
        $g = $this->bimbinganBerkas();

        $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('berkas.bimbingan', $g))->assertOk();
    }

    public function test_kaprodi_bisa_membuka(): void
    {
        $g = $this->bimbinganBerkas();

        $this->actingAs($this->userByUsername('kaprodi'))
            ->get(route('berkas.bimbingan', $g))->assertOk();
    }

    // ── Yang tidak berhak ───────────────────────────────────────────────────

    /** Inti kerentanannya: tanpa login sama sekali. */
    public function test_tamu_tanpa_login_ditolak(): void
    {
        $g = $this->bimbinganBerkas();

        $this->get(route('berkas.bimbingan', $g))->assertForbidden();
    }

    public function test_mahasiswa_lain_ditolak(): void
    {
        $g = $this->bimbinganBerkas();

        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get(route('berkas.bimbingan', $g))->assertForbidden();
    }

    /** Pembimbing industri hanya berhak atas mahasiswa yang dia dampingi. */
    public function test_industri_yang_tidak_mendampingi_ditolak(): void
    {
        Storage::fake('local');

        $student = $this->userByUsername('3.34.23.2.02')->student; // tanpa magang
        $path    = UploadedFile::fake()->create('lap.pdf', 20, 'application/pdf')
            ->store('reports', 'local');

        $report = InternshipReport::create([
            'student_id' => $student->id, 'title' => 'Lap',
            'file_path'  => $path, 'status' => 'pending',
        ]);

        $this->actingAs($this->userByUsername('industri1'))
            ->get(route('berkas.laporan', $report))->assertForbidden();
    }

    // ── Jalur aplikasi HP: URL bertanda tangan ──────────────────────────────

    /**
     * Aplikasi membuka berkas lewat browser luar yang tak membawa token, jadi
     * payload API memberi URL bertanda tangan berbatas waktu.
     */
    public function test_url_bertanda_tangan_bisa_dibuka_tanpa_login(): void
    {
        $g = $this->bimbinganBerkas();

        $url = URL::temporarySignedRoute('berkas.bimbingan', now()->addMinutes(30), [$g]);

        $this->get($url)->assertOk();
    }

    public function test_tanda_tangan_kedaluwarsa_ditolak(): void
    {
        $g = $this->bimbinganBerkas();

        $url = URL::temporarySignedRoute('berkas.bimbingan', now()->subMinute(), [$g]);

        $this->get($url)->assertForbidden();
    }

    public function test_tanda_tangan_yang_diutak_atik_ditolak(): void
    {
        $g = $this->bimbinganBerkas();

        $url = URL::temporarySignedRoute('berkas.bimbingan', now()->addMinutes(30), [$g]);

        $this->get($url . 'x')->assertForbidden();
    }

    // ── Payload & penyimpanan ───────────────────────────────────────────────

    /** API tak boleh lagi membocorkan URL /storage yang bisa dibuka siapa saja. */
    public function test_payload_api_tidak_lagi_memberi_url_storage_publik(): void
    {
        $g   = $this->bimbinganBerkas();
        $mhs = $this->userByUsername('3.34.23.2.01');

        $data = $this->actingAs($mhs, 'sanctum')
            ->getJson('/api/mahasiswa/bimbingan')->assertOk()->json();

        $url = collect($data['guidances'])->firstWhere('id', $g->id)['file_url'];

        $this->assertStringNotContainsString('/storage/', $url,
            'Payload masih memberi URL publik yang bisa dibuka tanpa login.');
        $this->assertStringContainsString('signature=', $url,
            'URL untuk aplikasi harus bertanda tangan agar bisa dibuka di browser luar.');
    }

    /** Unggahan baru harus mendarat di disk privat, bukan public/. */
    public function test_unggahan_baru_masuk_disk_privat(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $mhs = $this->magangBerjalan($this->userByUsername('3.34.23.2.01'));

        $this->actingAs($mhs)->post('/mahasiswa/bimbingan', [
            'title' => 'Dengan lampiran', 'activity' => 'a', 'date' => '2024-07-01',
            'file'  => UploadedFile::fake()->create('draft.pdf', 20, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $path = Guidance::where('title', 'Dengan lampiran')->firstOrFail()->name_file;

        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    /** Bukti penerimaan magang memuat data pribadi — hanya pemilik & Kaprodi. */
    public function test_bukti_magang_hanya_untuk_pemilik_dan_kaprodi(): void
    {
        Storage::fake('local');

        $student = $this->userByUsername('3.34.23.2.01')->student;
        $path    = UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf')
            ->store('magang-proofs', 'local');

        $req = CompanyRequest::create([
            'student_id' => $student->id, 'company_name' => 'PT Uji',
            'pic_name' => 'Budi', 'status' => 'pending', 'proof_file' => $path,
        ]);

        $this->actingAs($this->userByUsername('3.34.23.2.01'))
            ->get(route('berkas.bukti', $req))->assertOk();

        $this->actingAs($this->userByUsername('kaprodi'))
            ->get(route('berkas.bukti', $req))->assertOk();

        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get(route('berkas.bukti', $req))->assertForbidden();
    }
}
