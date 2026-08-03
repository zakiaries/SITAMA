<?php

namespace Tests\Feature\BlackBox;

use App\Models\AssessmentComponent;
use App\Models\Guidance;
use App\Models\InternshipReport;
use App\Models\LogBook;
use App\Models\Seminar;
use App\Models\SeminarAttendance;
use App\Models\SeminarPresenter;
use App\Models\StudentScore;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\FeatureTestCase;

/**
 * PENGUJIAN BLACK-BOX — TABEL 4.2 FITUR DOSEN PEMBIMBING
 *
 * Menjalankan skenario U-06, U-07, U-08, U-09, U-10, dan U-12 dari Tabel 3.25,
 * dari sudut pandang dosen pembimbing kampus.
 */
class PengujianDosenTest extends FeatureTestCase
{
    /** U-06 — Dosen melihat logbook mahasiswa dan memberi catatan. */
    public function test_u06_dosen_melihat_dan_mencatat_logbook(): void
    {
        $mhs   = $this->magangBerjalan($this->userByUsername('3.34.23.2.01'));
        $dosen = $this->userByUsername('dosen1');

        $lb = LogBook::create([
            'student_id' => $mhs->student->id, 'title' => 'Hari ke-1',
            'activity' => 'orientasi perusahaan', 'date' => '2024-07-01',
        ]);

        // Entri mahasiswa tampil di halaman detail dosen.
        $this->actingAs($dosen)->get("/dosen/mahasiswa/{$mhs->student->id}")
            ->assertOk()
            ->assertSee('Hari ke-1')
            ->assertSee('orientasi perusahaan');

        // Dosen memberi catatan pada entri tersebut.
        $this->actingAs($dosen)->post(
            "/dosen/mahasiswa/{$mhs->student->id}/logbook/{$lb->id}/note",
            ['note' => 'Lanjutkan, catat lebih rinci.']
        )->assertSessionHasNoErrors();

        $this->assertSame('Lanjutkan, catat lebih rinci.', $lb->fresh()->lecturer_note);

        // Catatan itu terbaca oleh mahasiswanya.
        $this->actingAs($mhs)->get('/mahasiswa/logbook')
            ->assertOk()
            ->assertSee('Lanjutkan, catat lebih rinci.');

        // Catatan bisa dihapus kembali.
        $this->actingAs($dosen)->delete(
            "/dosen/mahasiswa/{$mhs->student->id}/logbook/{$lb->id}/note"
        )->assertSessionHasNoErrors();

        $this->assertNull($lb->fresh()->lecturer_note);
    }

    /** U-07 — Dosen menyetujui bimbingan dan meminta revisi. */
    public function test_u07_dosen_menyetujui_dan_meminta_revisi_bimbingan(): void
    {
        $mhs   = $this->userByUsername('3.34.23.2.01');
        $dosen = $this->userByUsername('dosen1');

        $setuju = Guidance::create([
            'student_id' => $mhs->student->id, 'title' => 'Bimbingan A',
            'activity' => 'a', 'date' => '2024-07-01', 'status' => 'pending',
        ]);
        $revisi = Guidance::create([
            'student_id' => $mhs->student->id, 'title' => 'Bimbingan B',
            'activity' => 'b', 'date' => '2024-07-02', 'status' => 'pending',
        ]);

        $this->actingAs($dosen)->post(
            "/dosen/mahasiswa/{$mhs->student->id}/bimbingan/{$setuju->id}/approve",
            ['note' => 'Sudah baik.']
        )->assertSessionHasNoErrors();

        $this->actingAs($dosen)->post(
            "/dosen/mahasiswa/{$mhs->student->id}/bimbingan/{$revisi->id}/revisi",
            ['note' => 'Perbaiki metodologi.']
        )->assertSessionHasNoErrors();

        $this->assertSame('approved', $setuju->fresh()->status);
        $this->assertSame('rejected', $revisi->fresh()->status);
    }

    /** U-08 — Dosen meninjau laporan akhir: menyetujui dan meminta revisi. */
    public function test_u08_dosen_meninjau_laporan(): void
    {
        $mhs   = $this->userByUsername('3.34.23.2.01');
        $dosen = $this->userByUsername('dosen1');

        $laporan = InternshipReport::create([
            'student_id' => $mhs->student->id, 'title' => 'Laporan Akhir',
            'file_path' => 'reports/uji.pdf', 'status' => 'pending',
        ]);

        // Minta revisi lebih dulu.
        $this->actingAs($dosen)->post(
            "/dosen/mahasiswa/{$mhs->student->id}/laporan/{$laporan->id}/revisi",
            ['note' => 'Bab 4 belum lengkap.']
        )->assertSessionHasNoErrors();

        $this->assertSame('rejected', $laporan->fresh()->status);
        $this->assertSame('Bab 4 belum lengkap.', $laporan->fresh()->lecturer_note);

        // Setelah diperbaiki, disetujui.
        $this->actingAs($dosen)->post(
            "/dosen/mahasiswa/{$mhs->student->id}/laporan/{$laporan->id}/approve",
            ['note' => 'Disetujui.']
        )->assertSessionHasNoErrors();

        $this->assertSame('approved', $laporan->fresh()->status);
    }

    /** U-09 — Dosen mengisi nilai; nilai tampil pada rekapitulasi mahasiswa. */
    public function test_u09_dosen_mengisi_nilai(): void
    {
        // Nilai baru terbuka setelah mahasiswa merampungkan logbook & laporan —
        // di alur nyata dosen tak pernah bisa menilai sebelum itu.
        $mhs   = $this->siapDinilai($this->magangBerjalan($this->userByUsername('3.34.23.2.01')));
        $dosen = $this->userByUsername('dosen1');

        $this->actingAs($dosen)->get("/dosen/mahasiswa/{$mhs->student->id}/nilai")->assertOk();

        $komponen = AssessmentComponent::forScorer('lecturer')->with('detailedComponents')->get()
            ->flatMap->detailedComponents;

        $this->assertNotEmpty($komponen, 'Rubrik penilaian dosen belum tersedia.');

        $scores = $komponen->mapWithKeys(fn ($d) => [$d->id => 8])->all();

        $this->actingAs($dosen)->post(
            "/dosen/mahasiswa/{$mhs->student->id}/nilai",
            ['scores' => $scores]
        )->assertSessionHasNoErrors();

        $internship = $mhs->student->internships()->latest('id')->first();

        $this->assertSame(
            count($scores),
            StudentScore::where('internship_id', $internship->id)
                ->where('scorer_type', 'lecturer')->count(),
            'Nilai dosen tidak tersimpan lengkap.'
        );

        // Tampil pada rekapitulasi nilai mahasiswa.
        $this->actingAs($mhs)->get('/mahasiswa/nilai')->assertOk();
    }

    /** U-10 — Dosen membuat sesi seminar lalu menetapkan jadwal final. */
    public function test_u10_dosen_membuat_sesi_dan_menetapkan_jadwal(): void
    {
        $mhs   = $this->userByUsername('3.34.23.2.01');
        $dosen = $this->userByUsername('dosen1');

        $this->actingAs($dosen)->post('/dosen/seminar', [
            'title'       => 'Seminar Hasil Magang',
            'description' => 'Seminar hasil pelaksanaan magang.',
            'program'     => 'Teknik Informatika',
            'organizer'   => 'Politeknik Negeri Semarang',
            'student_ids' => [$mhs->student->id],
        ])->assertSessionHasNoErrors();

        $seminar = Seminar::where('title', 'Seminar Hasil Magang')->firstOrFail();
        $this->assertSame('draft', $seminar->status);

        // Menetapkan jadwal final.
        $tanggal = now()->addDays(7)->toDateString();

        $this->actingAs($dosen)->post("/dosen/seminar/{$seminar->id}/finalize", [
            'date'     => $tanggal,
            'time'     => '09.00 - 11.00',
            'location' => 'Ruang Seminar TI-01',
        ])->assertSessionHasNoErrors();

        $seminar->refresh();
        $this->assertSame('scheduled', $seminar->status, 'Status sesi tidak berubah menjadi terjadwal.');
        $this->assertSame($tanggal, $seminar->date->toDateString());
        $this->assertSame('Ruang Seminar TI-01', $seminar->location);
    }

    /** U-12 — Mengunduh berita acara seminar berformat PDF berisi daftar hadir. */
    public function test_u12_berita_acara_pdf_berisi_daftar_hadir(): void
    {
        $penyaji = $this->userByUsername('3.34.23.2.01');
        $audiens = $this->userByUsername('3.34.23.2.02');
        $dosen   = $this->userByUsername('dosen1');

        $seminar = Seminar::create([
            'lecturer_id'  => $dosen->lecturer->id,
            'title'        => 'Seminar Berita Acara',
            'program'      => 'Teknik Informatika',
            'organizer'    => 'Polines',
            'status'       => 'scheduled',
            'date'         => now()->toDateString(),
            'time'         => '09.00 - 11.00',
            'location'     => 'Ruang TI-01',
            'access_token' => Str::random(48),
        ]);
        SeminarPresenter::create(['seminar_id' => $seminar->id, 'student_id' => $penyaji->student->id]);
        SeminarAttendance::create([
            'seminar_id' => $seminar->id,
            'student_id' => $audiens->student->id,
            'name'       => $audiens->name,
            'nim'        => $audiens->username,
        ]);

        $response = $this->actingAs($penyaji)
            ->get("/mahasiswa/seminar/{$seminar->id}/berita-acara")
            ->assertOk();

        $this->assertSame('application/pdf', $response->headers->get('content-type'),
            'Berita acara tidak diunduh sebagai PDF.');

        $isi = $response->getContent();
        $this->assertStringStartsWith('%PDF', $isi, 'Berkas yang diunduh bukan PDF yang sah.');
        $this->assertGreaterThan(1000, strlen($isi), 'Isi PDF terlalu kecil untuk memuat daftar hadir.');
    }
}
