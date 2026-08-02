<?php

namespace Tests\Feature\BlackBox;

use App\Models\AssessmentComponent;
use App\Models\InvitationToken;
use App\Models\Lecturer;
use App\Models\LogBook;
use App\Models\StudentScore;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\FeatureTestCase;

/**
 * PENGUJIAN BLACK-BOX — TABEL 4.3 FITUR PEMBIMBING INDUSTRI
 *
 * Menjalankan skenario U-02, U-06, dan U-09 dari Tabel 3.25.
 */
class PengujianIndustriTest extends FeatureTestCase
{
    /** U-02 — Membuka tautan undangan, mengaktivasi akun, lalu login. */
    public function test_u02_aktivasi_akun_lewat_tautan_undangan(): void
    {
        // Akun dibuat Kaprodi saat menyetujui pengajuan: belum aktif, dan
        // usernamenya masih sementara (pending_xxx) karena yang bersangkutan
        // menentukan sendiri saat aktivasi.
        $calon = User::create([
            'name'         => 'Pembimbing Industri Uji',
            'username'     => 'pending_' . Str::random(10),
            'email'        => 'pic-uji@perusahaan.test',
            'password'     => Hash::make(Str::random(32)),
            'role'         => 'lecturer_industry',
            'is_activated' => false,
        ]);
        Lecturer::create(['user_id' => $calon->id]);

        $undangan = InvitationToken::create([
            'user_id'    => $calon->id,
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        // Halaman aktivasi terbuka tanpa perlu login.
        $this->get("/aktivasi/{$undangan->token}")->assertOk();

        // Pembimbing industri menentukan sendiri username & kata sandinya di sini.
        $this->post("/aktivasi/{$undangan->token}", [
            'username'              => 'pic-uji',
            'password'              => 'SandiIndustri8',
            'password_confirmation' => 'SandiIndustri8',
        ])->assertSessionHasNoErrors();

        $this->assertTrue($calon->fresh()->is_activated, 'Akun tidak berubah menjadi aktif.');
        $this->assertNotNull($undangan->fresh()->used_at, 'Tautan undangan tidak ditandai terpakai.');

        // Akun itu kini bisa dipakai login.
        $this->post('/logout');
        $this->post('/login', [
            'username' => 'pic-uji',
            'password' => 'SandiIndustri8',
        ])->assertRedirect('/dosen-industri/dashboard');

        $this->assertAuthenticatedAs($calon->fresh());
    }

    /**
     * Tautan undangan sekali pakai.
     *
     * Ditemukan saat pengujian U-02: ActivationController sudah memanggil
     * update(['used_at' => now()]), tetapi `used_at` tak terdaftar di $fillable
     * sehingga dibuang diam-diam. Akibatnya tautan tetap sah sampai kedaluwarsa
     * dan siapa pun yang memegangnya — misalnya dari email yang diteruskan —
     * bisa memakainya lagi untuk mengganti username dan kata sandi akun itu.
     */
    public function test_u02_tautan_undangan_tak_bisa_dipakai_dua_kali(): void
    {
        $calon = User::create([
            'name'         => 'PIC Sekali Pakai',
            'username'     => 'pending_' . Str::random(10),
            'email'        => 'sekali@perusahaan.test',
            'password'     => Hash::make(Str::random(32)),
            'role'         => 'lecturer_industry',
            'is_activated' => false,
        ]);
        Lecturer::create(['user_id' => $calon->id]);

        $undangan = InvitationToken::create([
            'user_id'    => $calon->id,
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        $this->post("/aktivasi/{$undangan->token}", [
            'username'              => 'pic-sah',
            'password'              => 'SandiPertama8',
            'password_confirmation' => 'SandiPertama8',
        ])->assertSessionHasNoErrors();

        // Percobaan kedua dengan tautan yang sama harus ditolak.
        $this->post("/aktivasi/{$undangan->token}", [
            'username'              => 'pic-pembajak',
            'password'              => 'SandiPembajak8',
            'password_confirmation' => 'SandiPembajak8',
        ])->assertRedirect(route('login'));

        $calon->refresh();
        $this->assertSame('pic-sah', $calon->username,
            'Tautan lama masih bisa dipakai mengganti username akun.');
        $this->assertTrue(Hash::check('SandiPertama8', $calon->password),
            'Tautan lama masih bisa dipakai mengganti kata sandi akun.');
    }

    /** U-06 — Pembimbing industri melihat logbook dan memberi komentar. */
    public function test_u06_industri_melihat_dan_mengomentari_logbook(): void
    {
        $mhs      = $this->magangBerjalan($this->userByUsername('3.34.23.2.01'));
        $industri = $this->userByUsername('industri1');

        $lb = LogBook::create([
            'student_id' => $mhs->student->id, 'title' => 'Hari ke-2',
            'activity' => 'mempelajari alur kerja tim', 'date' => '2024-07-02',
        ]);

        $this->actingAs($industri)->get("/dosen-industri/mahasiswa/{$mhs->student->id}")
            ->assertOk()
            ->assertSee('Hari ke-2')
            ->assertSee('mempelajari alur kerja tim');

        $this->actingAs($industri)->post(
            "/dosen-industri/mahasiswa/{$mhs->student->id}/logbook/{$lb->id}/komentar",
            ['komentar' => 'Kerja bagus, pertahankan.']
        )->assertSessionHasNoErrors();

        $this->assertSame('Kerja bagus, pertahankan.', $lb->fresh()->industry_note);

        // Komentar terbaca oleh mahasiswanya.
        $this->actingAs($mhs)->get('/mahasiswa/logbook')
            ->assertOk()
            ->assertSee('Kerja bagus, pertahankan.');

        // Dan bisa dihapus kembali.
        $this->actingAs($industri)->delete(
            "/dosen-industri/mahasiswa/{$mhs->student->id}/logbook/{$lb->id}/komentar"
        )->assertSessionHasNoErrors();

        $this->assertNull($lb->fresh()->industry_note);
    }

    /** U-09 — Pembimbing industri mengisi nilai sesuai komponen perannya. */
    public function test_u09_industri_mengisi_nilai(): void
    {
        $mhs      = $this->magangBerjalan($this->userByUsername('3.34.23.2.01'));
        $industri = $this->userByUsername('industri1');

        $this->actingAs($industri)
            ->get("/dosen-industri/mahasiswa/{$mhs->student->id}/penilaian")->assertOk();

        $komponen = AssessmentComponent::forScorer('lecturer_industry')
            ->with('detailedComponents')->get()->flatMap->detailedComponents;

        $this->assertNotEmpty($komponen, 'Rubrik penilaian pembimbing industri belum tersedia.');

        $scores = $komponen->mapWithKeys(fn ($d) => [$d->id => 9])->all();

        $this->actingAs($industri)->post(
            "/dosen-industri/mahasiswa/{$mhs->student->id}/penilaian",
            ['scores' => $scores, 'performance_notes' => 'Disiplin dan mampu bekerja mandiri.']
        )->assertSessionHasNoErrors();

        $internship = $mhs->student->internships()->latest('id')->first();

        $this->assertSame(
            count($scores),
            StudentScore::where('internship_id', $internship->id)
                ->where('scorer_type', 'lecturer_industry')->count(),
            'Nilai pembimbing industri tidak tersimpan lengkap.'
        );

        // Nilainya terpisah dari nilai dosen dan tampil di rekapitulasi mahasiswa.
        $this->assertSame(0, StudentScore::where('internship_id', $internship->id)
            ->where('scorer_type', 'lecturer')->count(),
            'Nilai industri tercatat sebagai nilai dosen.');

        $this->actingAs($mhs)->get('/mahasiswa/nilai')->assertOk();
    }
}
