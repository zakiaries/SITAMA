<?php

namespace Tests\Feature;

use App\Models\Guidance;
use App\Models\Internship;
use App\Models\InternshipReport;
use App\Models\LogBook;
use App\Models\User;
use Tests\FeatureTestCase;

/**
 * Paritas API mobile dengan perbaikan di web.
 *
 * Perbaikan sisi web tak otomatis sampai ke aplikasi: foto profil, penanda
 * "menunggu tanggapan", dan detail mahasiswa portal industri semuanya butuh
 * field baru di payload API. Field lama SENGAJA tidak diubah agar APK yang
 * sudah beredar tetap jalan.
 */
class ApiParitasWebTest extends FeatureTestCase
{
    private function token(string $username): string
    {
        return $this->userByUsername($username)->createToken('uji')->plainTextToken;
    }

    private function api(string $username)
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token($username));
    }

    public function test_foto_mahasiswa_ikut_di_daftar_dan_detail_dosen(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.01');
        $mhs->update(['photo_profile' => 'photos/uji.jpg']);

        $this->api('dosen1')->getJson('/api/dosen/dashboard')
            ->assertOk()
            ->assertJsonPath('students.0.photo_url', fn ($v) => str_contains((string) $v, 'photos/uji.jpg'));

        $this->api('dosen1')->getJson("/api/dosen/mahasiswa/{$mhs->student->id}")
            ->assertOk()
            ->assertJsonPath('student.photo_url', fn ($v) => str_contains((string) $v, 'photos/uji.jpg'));
    }

    public function test_foto_mahasiswa_ikut_di_portal_industri(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.01');
        $mhs->update(['photo_profile' => 'photos/uji.jpg']);

        $this->api('industri1')->getJson('/api/dosen-industri/dashboard')
            ->assertOk()
            ->assertJsonPath('students.0.photo_url', fn ($v) => str_contains((string) $v, 'photos/uji.jpg'));

        $this->api('industri1')->getJson("/api/dosen-industri/mahasiswa/{$mhs->student->id}")
            ->assertOk()
            ->assertJsonPath('student.photo_url', fn ($v) => str_contains((string) $v, 'photos/uji.jpg'));
    }

    public function test_penanda_menunggu_tanggapan_dosen(): void
    {
        $student = $this->userByUsername('3.34.23.2.01')->student;

        LogBook::create(['student_id' => $student->id, 'title' => 'L1', 'activity' => 'a', 'date' => '2024-02-01']);
        Guidance::create(['student_id' => $student->id, 'title' => 'B1', 'activity' => 'a', 'date' => '2024-02-01', 'status' => 'pending']);
        InternshipReport::create(['student_id' => $student->id, 'title' => 'Lap', 'file_path' => 'reports/x.pdf', 'status' => 'pending']);

        $this->api('dosen1')->getJson('/api/dosen/dashboard')
            ->assertOk()
            ->assertJsonPath('menunggu_tanggapan.logbook', 1)
            ->assertJsonPath('menunggu_tanggapan.bimbingan', 1)
            ->assertJsonPath('menunggu_tanggapan.laporan', 1)
            ->assertJsonPath('menunggu_tanggapan.total', 3)
            ->assertJsonPath('students.0.perlu_tanggapan', 3);
    }

    public function test_penanda_menunggu_tanggapan_industri(): void
    {
        $student = $this->userByUsername('3.34.23.2.01')->student;

        LogBook::create(['student_id' => $student->id, 'title' => 'L1', 'activity' => 'a', 'date' => '2024-02-01']);
        LogBook::create(['student_id' => $student->id, 'title' => 'L2', 'activity' => 'b', 'date' => '2024-02-02', 'industry_note' => 'ok']);

        $this->api('industri1')->getJson('/api/dosen-industri/dashboard')
            ->assertOk()
            ->assertJsonPath('stats.belum_dikomen', 1)
            ->assertJsonPath('students.0.logbook_belum_dikomen', 1);
    }

    public function test_detail_industri_memuat_konteks_lengkap(): void
    {
        $studentId = $this->userByUsername('3.34.23.2.01')->student->id;
        $dosen     = $this->userByUsername('dosen1');

        $this->api('industri1')->getJson("/api/dosen-industri/mahasiswa/{$studentId}")
            ->assertOk()
            ->assertJsonPath('student.email', $this->userByUsername('3.34.23.2.01')->email)
            ->assertJsonPath('student.study_program', 'Teknik Informatika')
            ->assertJsonPath('student.academic_year', '2023/2024')
            ->assertJsonPath('lecturer.name', $dosen->name)
            ->assertJsonPath('lecturer.email', $dosen->email)
            ->assertJsonPath('stats.logbook_minimum', Internship::MIN_LOGBOOK)
            ->assertJsonPath('penilaian.komponen_total', 8)
            ->assertJsonPath('penilaian.komponen_dinilai', 0)
            ->assertJsonPath('penilaian.rata_rata', null);
    }

    /**
     * Field lama tak boleh hilang — APK yang sudah beredar masih memakainya.
     * Dipisah per peran: guard auth menahan user dari request pertama, jadi
     * dua peran dalam satu tes akan ditolak 403.
     */
    public function test_field_lama_dosen_tetap_ada(): void
    {
        $this->api('dosen1')->getJson('/api/dosen/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'lecturer', 'counts', 'majors', 'years',
                'students' => [['id', 'name', 'username', 'major', 'the_class', 'company', 'guidances_count', 'logbooks_count', 'status']],
            ]);
    }

    /**
     * Catatan logbook dosen di mobile mengikuti web: wajib berisi, dan
     * dikosongkan lewat tombol hapus (bukan menyimpan catatan kosong).
     */
    public function test_catatan_logbook_dosen_wajib_isi_dan_bisa_dihapus(): void
    {
        $student = $this->userByUsername('3.34.23.2.01')->student;
        $lb = LogBook::create([
            'student_id' => $student->id,
            'title'      => 'Hari ke-1',
            'activity'   => 'Perkenalan tim.',
            'date'       => now()->subDay(),
        ]);

        $this->api('dosen1')
            ->postJson("/api/dosen/mahasiswa/{$student->id}/logbook/{$lb->id}/note", ['note' => ''])
            ->assertStatus(422);

        $this->api('dosen1')
            ->postJson("/api/dosen/mahasiswa/{$student->id}/logbook/{$lb->id}/note", ['note' => 'Kerja bagus.'])
            ->assertOk();
        $this->assertSame('Kerja bagus.', $lb->fresh()->lecturer_note);

        $this->api('dosen1')
            ->deleteJson("/api/dosen/mahasiswa/{$student->id}/logbook/{$lb->id}/note")
            ->assertOk();
        $this->assertNull($lb->fresh()->lecturer_note);
    }

    /** Kartu seminar membawa penanda kunci supaya UI mobile bisa menyembunyikan aksi. */
    public function test_kartu_seminar_membawa_penanda_terkunci(): void
    {
        $dosen = $this->userByUsername('dosen1');

        \App\Models\Seminar::create([
            'lecturer_id'  => $dosen->lecturer->id,
            'title'        => 'Sudah lewat',
            'program'      => 'TI',
            'status'       => 'scheduled',
            'date'         => now()->subDay()->toDateString(),
            'location'     => 'TI-01',
            'access_token' => \Illuminate\Support\Str::random(48),
        ]);

        $this->api('dosen1')->getJson('/api/dosen/seminar')
            ->assertOk()
            ->assertJsonPath('sessions.0.date_passed', true)
            ->assertJsonPath('sessions.0.can_edit', false)
            ->assertJsonPath('sessions.0.hadir_url', null);
    }

    public function test_field_lama_industri_tetap_ada(): void
    {
        $this->api('industri1')->getJson('/api/dosen-industri/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'lecturer',
                'stats' => ['total_mahasiswa', 'aktif', 'belum_dikomen'],
                'students' => [['id', 'name', 'username', 'the_class', 'position', 'company', 'logbook_total', 'logbook_dikomen', 'is_finished']],
            ]);
    }
}
