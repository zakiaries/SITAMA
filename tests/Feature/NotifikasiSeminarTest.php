<?php

namespace Tests\Feature;

use App\Models\Guidance;
use App\Models\InternshipReport;
use App\Models\LogBook;
use App\Models\Notification;
use App\Models\Seminar;
use App\Models\SeminarPresenter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\FeatureTestCase;

/**
 * Tiga laporan bug dari uji coba:
 *  1. Lonceng notifikasi kosong padahal penanda di sidebar menyala — sebagian
 *     besar kejadian memang tak pernah membuat Notification.
 *  2. Menu dosen bernama "Seminar Bimbingan", padahal isinya seminar hasil
 *     magang.
 *  3. Tanggal seminar yang sudah terjadwal masih bisa diubah; seharusnya hanya
 *     jam dan lokasi.
 */
class NotifikasiSeminarTest extends FeatureTestCase
{
    private function mahasiswa()
    {
        return $this->userByUsername('3.34.23.2.01');
    }

    public function test_bimbingan_baru_memberi_notifikasi_ke_dosen(): void
    {
        Storage::fake('public');
        $mhs   = $this->magangBerjalan($this->mahasiswa());
        $dosen = $this->userByUsername('dosen1');

        $this->actingAs($mhs)->post('/mahasiswa/bimbingan', [
            'title' => 'Konsultasi proposal', 'activity' => 'bahas bab 1', 'date' => '2024-07-01',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Notification::where('user_id', $dosen->id)->where('category', 'bimbingan')->count());
    }

    public function test_laporan_diunggah_memberi_notifikasi_ke_dosen(): void
    {
        Storage::fake('public');
        $mhs   = $this->mahasiswa();
        $dosen = $this->userByUsername('dosen1');

        $this->actingAs($mhs)->post('/mahasiswa/laporan', [
            'title' => 'Laporan Akhir',
            'file'  => UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Notification::where('user_id', $dosen->id)->where('category', 'laporan')->count());
    }

    public function test_acc_dan_revisi_dosen_memberi_notifikasi_ke_mahasiswa(): void
    {
        // ACC/revisi dosen terkunci setelah magang ditutup Kaprodi, jadi
        // notifikasinya diuji pada magang yang masih berjalan.
        $mhs      = $this->magangBerjalan($this->mahasiswa());
        $student  = $mhs->student;
        $dosen    = $this->userByUsername('dosen1');

        $bim = Guidance::create(['student_id' => $student->id, 'title' => 'B1', 'activity' => 'a', 'date' => '2024-07-01', 'status' => 'pending']);

        $this->actingAs($dosen)->post("/dosen/mahasiswa/{$student->id}/bimbingan/{$bim->id}/approve", ['note' => 'Bagus.']);
        $this->assertSame(1, Notification::where('user_id', $mhs->id)->where('category', 'bimbingan')->count());

        $rep = InternshipReport::create(['student_id' => $student->id, 'title' => 'Lap', 'file_path' => 'reports/x.pdf', 'status' => 'pending']);
        $this->actingAs($dosen)->post("/dosen/mahasiswa/{$student->id}/laporan/{$rep->id}/revisi", ['note' => 'Perbaiki bab 3.']);
        $this->assertSame(1, Notification::where('user_id', $mhs->id)->where('category', 'laporan')->count());
    }

    public function test_komentar_industri_memberi_notifikasi_ke_mahasiswa(): void
    {
        $mhs     = $this->mahasiswa();
        $student = $mhs->student;
        $lb      = LogBook::create(['student_id' => $student->id, 'title' => 'L1', 'activity' => 'a', 'date' => '2024-07-01']);

        // Magang fixture-nya sudah is_finished, dan komentar logbook kini terkunci
        // untuk magang yang selesai. Di alur nyata komentar terjadi saat magang
        // masih berjalan, jadi kondisi itu yang dibuat di sini.
        $student->internships()->latest('id')->first()->update(['is_finished' => false]);

        $this->actingAs($this->userByUsername('industri1'))
            ->post("/dosen-industri/mahasiswa/{$student->id}/logbook/{$lb->id}/komentar", ['komentar' => 'Rapi.'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Notification::where('user_id', $mhs->id)->where('category', 'log_book')->count());
    }

    public function test_plot_dospem_memberi_notifikasi_ke_mahasiswa(): void
    {
        $mhs   = $this->userByUsername('3.34.23.2.03'); // pending, belum punya dospem
        $dosen = $this->userByUsername('dosen1')->lecturer;

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/mahasiswa/{$mhs->student->id}/assign-lecturer", ['lecturer_id' => $dosen->id]);

        $this->assertSame(1, Notification::where('user_id', $mhs->id)->count());
    }

    public function test_menu_dosen_bernama_seminar_magang(): void
    {
        $this->actingAs($this->userByUsername('dosen1'))
            ->get('/dosen/seminar')
            ->assertOk()
            ->assertSee('Seminar Magang')
            ->assertDontSee('Seminar Bimbingan');
    }

    /**
     * Sesi terjadwal: tanggal, jam, dan lokasi semuanya boleh berubah.
     *
     * Dulu tanggal dikunci sekali tetapkan. Aturan itu dicabut karena menjebak
     * dosen yang berhalangan: sesinya tertinggal di tanggal yang telanjur lewat
     * dan hanya bisa dibatalkan lalu dibuat ulang dari nol. Kekhawatiran
     * aslinya — penyaji sudah menyiapkan diri untuk tanggal lama — ditangani
     * lewat pemberitahuan yang menyebutkan jadwal lamanya, bukan lewat
     * penguncian.
     */
    public function test_tanggal_seminar_terjadwal_bisa_dipindahkan(): void
    {
        $dosen   = $this->userByUsername('dosen1');
        $student = $this->mahasiswa()->student;

        $seminar = Seminar::create([
            'lecturer_id' => $dosen->lecturer->id,
            'title'       => 'Seminar Hasil Magang',
            'program'     => 'Teknik Informatika',
            'organizer'   => $dosen->name,
            'status'      => 'scheduled',
            'date'        => now()->addDays(7)->toDateString(),
            'time'        => '09.00 - 11.00',
            'location'    => 'Ruang A',
            'min_guests' => 15,
        ]);
        SeminarPresenter::create(['seminar_id' => $seminar->id, 'student_id' => $student->id]);

        $tanggalBaru = now()->addDays(30)->toDateString();

        $this->actingAs($dosen)->post("/dosen/seminar/{$seminar->id}/finalize", [
            'date'     => $tanggalBaru,
            'time'     => '13.00 - 15.00',
            'location' => 'Ruang B',
            'min_guests' => 15,
        ]);

        $seminar->refresh();
        $this->assertSame($tanggalBaru, $seminar->date->toDateString(), 'Tanggal seharusnya ikut pindah.');
        $this->assertSame('13.00 - 15.00', $seminar->time);
        $this->assertSame('Ruang B', $seminar->location);
    }
}
