<?php

namespace Tests\Feature;

use App\Models\Internship;
use App\Models\Notification;
use App\Models\Seminar;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\FeatureTestCase;

/**
 * Delapan tempat dulu memanggil Notification::create([...]) langsung, melewati
 * helper kirim(), sehingga kolom `link` tak pernah terisi — notifikasinya lahir
 * tanpa tujuan dan diklik hanya memantulkan balik ke halaman notifikasi.
 *
 * Tes ini mengunci tujuan tiap kejadian, termasuk jalur mobile yang paling
 * mudah terlupa karena tak terlihat saat menguji lewat web.
 */
class NotifikasiPunyaTujuanTest extends FeatureTestCase
{
    private function linkTerakhir(int $userId, string $category): ?string
    {
        return Notification::where('user_id', $userId)
            ->where('category', $category)
            ->latest('id')->firstOrFail()->link;
    }

    /** Tidak boleh ada notifikasi baru yang lahir tanpa tujuan. */
    public function test_membuka_sesi_seminar_mengarahkan_penyaji_ke_seminarnya(): void
    {
        $dosen = $this->userByUsername('dosen1');
        $mhs   = $this->userByUsername('3.34.23.2.01');

        $this->actingAs($dosen)->post('/dosen/seminar', [
            'title'       => 'Seminar Uji Tujuan',
            'description' => 'uji',
            'program'     => 'Teknik Informatika',
            'organizer'   => 'Polines',
            'student_ids' => [$mhs->student->id],
        ])->assertSessionHasNoErrors();

        $seminar = Seminar::where('title', 'Seminar Uji Tujuan')->firstOrFail();

        $this->assertSame(
            "/mahasiswa/seminar/{$seminar->id}",
            $this->linkTerakhir($mhs->id, 'seminar')
        );
    }

    public function test_pengajuan_magang_mengarahkan_kaprodi_ke_halaman_pengajuan(): void
    {
        Storage::fake('public');
        $mhs = $this->userByUsername('3.34.23.2.02');

        $this->actingAs($mhs)->post('/mahasiswa/ajukan-magang', [
            'company_name' => 'PT Uji Tujuan',
            'pic_name'     => 'Budi',
            'pic_email'    => 'budi@uji.test',
            'pic_phone'    => '081234567890',
            'position'     => 'Developer',
            'bidang'       => 'IT',
            'start_date'   => now()->addMonth()->toDateString(),
            'end_date'     => now()->addMonths(4)->toDateString(),
            'proof_file'   => \Illuminate\Http\UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $kaprodi = $this->userByUsername('kaprodi');

        $this->assertSame(
            '/kaprodi/pengajuan-magang',
            $this->linkTerakhir($kaprodi->id, 'pengajuan_magang')
        );
    }

    /**
     * Sengaja ke detail mahasiswa, bukan daftar: tombol ACC selesai magang ada di
     * sana, dan tak ada tab di daftar yang menampilkan pengajuan semacam ini.
     */
    public function test_ajukan_selesai_magang_mengarahkan_kaprodi_ke_detail_mahasiswa(): void
    {
        $mhs     = $this->userByUsername('3.34.23.2.01');
        $kaprodi = $this->userByUsername('kaprodi');
        $student = $mhs->student;

        // Fixture-nya sudah selesai; kembalikan ke berjalan agar bisa diajukan.
        $internship = $student->internships()->latest('id')->first();
        $internship->update([
            'is_finished'      => false,
            'finish_requested' => false,
            'certificate_path' => 'certificates/uji.pdf',
        ]);

        // Penuhi seluruh checklist supaya pengajuannya benar-benar lewat.
        \App\Models\InternshipReport::create([
            'student_id' => $student->id, 'title' => 'Laporan Uji',
            'file_path'  => 'reports/uji.pdf', 'status' => 'approved',
        ]);

        $komponen = \App\Models\DetailedAssessmentComponent::firstOrFail();
        foreach (['lecturer', 'lecturer_industry'] as $penilai) {
            \App\Models\StudentScore::create([
                'internship_id' => $internship->id,
                'detailed_assessment_component_id' => $komponen->id,
                'scorer_type'   => $penilai, 'score' => 8,
            ]);
        }

        for ($i = 1; $i <= Internship::MIN_LOGBOOK; $i++) {
            \App\Models\LogBook::create([
                'student_id' => $student->id, 'title' => "Hari ke-{$i}",
                'activity'   => 'kegiatan', 'date' => now()->subDays($i)->toDateString(),
            ]);
        }

        $this->actingAs($mhs)->post('/mahasiswa/magang-saya/ajukan-selesai')
            ->assertSessionHasNoErrors();

        $this->assertTrue($internship->fresh()->finish_requested,
            'Pengajuan selesai magang tidak tersimpan — checklist fixture belum lengkap.');

        $this->assertSame(
            "/kaprodi/mahasiswa/{$student->id}",
            $this->linkTerakhir($kaprodi->id, 'selesai_magang')
        );
    }

    /**
     * Jalur mobile: dosen & pembimbing industri harus diarahkan ke logbook yang
     * sama tapi di portalnya masing-masing — dulu keduanya lahir tanpa tujuan.
     */
    public function test_logbook_via_api_memberi_tujuan_berbeda_untuk_dosen_dan_industri(): void
    {
        $mhs      = $this->magangBerjalan($this->userByUsername('3.34.23.2.01'));
        $dosen    = $this->userByUsername('dosen1');
        $industri = $this->userByUsername('industri1');

        $this->actingAs($mhs, 'sanctum')->postJson('/api/mahasiswa/logbook', [
            'title'    => 'Logbook via API',
            'activity' => 'uji tujuan notifikasi',
            'date'     => '2024-02-01',
        ])->assertSuccessful();

        $lb = \App\Models\LogBook::where('title', 'Logbook via API')->firstOrFail();

        $this->assertSame(
            "/dosen/mahasiswa/{$mhs->student->id}#logbook-{$lb->id}",
            $this->linkTerakhir($dosen->id, 'log_book')
        );

        $this->assertSame(
            "/dosen-industri/mahasiswa/{$mhs->student->id}#logbook-{$lb->id}",
            $this->linkTerakhir($industri->id, 'log_book')
        );
    }

    /** Penjaga menyeluruh: tak boleh ada Notification::create langsung lagi. */
    public function test_tak_ada_lagi_notification_create_langsung(): void
    {
        $temuan = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(app_path(), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            if (str_contains(file_get_contents($file->getPathname()), 'Notification::create(')) {
                $temuan[] = str_replace(app_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }

        $this->assertSame([], $temuan,
            "Pakai Notification::kirim() agar kolom `link` ikut terisi — Notification::create() melewatinya.");
    }
}
