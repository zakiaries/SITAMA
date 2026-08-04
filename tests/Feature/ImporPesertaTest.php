<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Tests\FeatureTestCase;

/**
 * Kaprodi tak punya layar untuk membuat akun mahasiswa — satu-satunya jalur
 * adalah mahasiswa mendaftar sendiri lalu disetujui. Untuk uji coba dengan
 * puluhan peserta sekaligus itu tak praktis, dan plotting dosen pembimbingnya
 * memang ditetapkan prodi, bukan hasil pendaftaran.
 *
 * Perintah impor menutup celah itu. Yang dijaga tes ini: idempoten (dijalankan
 * ulang tak menggandakan dan tak mengacak ulang sandi yang sudah dibagikan),
 * mengenali dosen yang sudah ada meski format gelarnya berbeda, dan tak
 * memaksakan data magang bagi mahasiswa yang tempatnya belum pasti.
 */
class ImporPesertaTest extends FeatureTestCase
{
    private string $berkas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->berkas = storage_path('app/uji-peserta.csv');
    }

    protected function tearDown(): void
    {
        @unlink($this->berkas);
        parent::tearDown();
    }

    private function tulisCsv(array $baris): void
    {
        $kolom = ['nim', 'nama', 'kelas', 'dospem', 'perusahaan',
                  'kota', 'mulai', 'selesai', 'bidang', 'pembimbing_industri'];

        $f = fopen($this->berkas, 'w');
        fputcsv($f, $kolom);
        foreach ($baris as $b) {
            fputcsv($f, array_map(fn ($k) => $b[$k] ?? '', $kolom));
        }
        fclose($f);
    }

    private function impor(array $opsi = []): \Illuminate\Testing\PendingCommand
    {
        return $this->artisan('simama:impor-peserta', array_merge([
            '--berkas' => $this->berkas,
        ], $opsi));
    }

    private function barisLengkap(array $ganti = []): array
    {
        return array_merge([
            'nim'                 => '4.33.23.0.01',
            'nama'                => 'ADRIANSYAH ALFARISYI',
            'kelas'               => 'TI - 3A',
            'dospem'              => 'DR. SUKAMTO, S.KOM., M.T.',
            'perusahaan'          => 'PT DYNAMIC TALENTA NAVIGATOR',
            'kota'                => 'Semarang',
            'mulai'               => '2026-08-03',
            'selesai'             => '2026-12-31',
            'bidang'              => 'Frontend Developer',
            'pembimbing_industri' => 'Bagus Kuncoro Aziz',
        ], $ganti);
    }

    // ── Pembuatan akun ──────────────────────────────────────────────────────

    public function test_akun_mahasiswa_dibuat_aktif_dan_terplot(): void
    {
        $this->tulisCsv([$this->barisLengkap()]);
        $this->impor()->assertSuccessful();

        $user = User::where('username', '4.33.23.0.01')->firstOrFail();

        $this->assertSame('student', $user->role);
        $this->assertSame('ADRIANSYAH ALFARISYI', $user->name);

        $student = $user->student;
        $this->assertSame('active', $student->status, 'Mahasiswa terdampar di halaman menunggu.');
        $this->assertNotNull($student->lecturer_id, 'Dosen pembimbing tak terplot.');
        $this->assertSame('Teknologi Rekayasa Komputer', $student->study_program);
        $this->assertSame('2026/2027', $student->academic_year);
    }

    /**
     * Daftar plotting hanya memuat nomor HP. Alamat karangan di domain kampus
     * berbahaya: bila alamat itu ternyata ada dan dipakai orang lain, tautan
     * reset kata sandi mahasiswa terkirim ke pihak yang tak berhak. Domain
     * .local membuat pengiriman mustahil sejak awal.
     */
    public function test_email_memakai_placeholder_bukan_domain_kampus(): void
    {
        $this->tulisCsv([$this->barisLengkap()]);
        $this->impor()->assertSuccessful();

        $email = User::where('username', '4.33.23.0.01')->firstOrFail()->email;

        $this->assertStringEndsWith('@simama.local', $email);
        $this->assertStringNotContainsString('polines.ac.id', $email,
            'Email karangan di domain kampus bisa mengirim tautan reset ke orang lain.');
    }

    /**
     * Pola kampus: namadepan.nimtanpatitik@domain
     * (contoh nyata: lanang.33423212@mhs.polines.ac.id).
     */
    public function test_email_kampus_disusun_dari_nama_dan_nim(): void
    {
        $this->tulisCsv([$this->barisLengkap()]);
        $this->impor(['--email-kampus' => 'mhs.polines.ac.id'])->assertSuccessful();

        $this->assertSame(
            'adriansyah.43323001@mhs.polines.ac.id',
            User::where('username', '4.33.23.0.01')->firstOrFail()->email
        );
    }

    /** Inisial dilewati: "M. MUCHLAS HUDAWAN" -> muchlas, bukan m. */
    public function test_inisial_dilewati_saat_menyusun_email(): void
    {
        $this->tulisCsv([$this->barisLengkap([
            'nim' => '4.33.22.1.13', 'nama' => 'M. MUCHLAS HUDAWAN',
        ])]);
        $this->impor(['--email-kampus' => 'mhs.polines.ac.id'])->assertSuccessful();

        $this->assertSame(
            'muchlas.43322113@mhs.polines.ac.id',
            User::where('username', '4.33.22.1.13')->firstOrFail()->email
        );
    }

    /**
     * NIM ikut di dalam alamat, dan NIM itu unik — jadi alamat yang polanya
     * meleset pun tak mungkin jatuh ke kotak masuk mahasiswa lain.
     */
    public function test_email_kampus_selalu_unik_per_mahasiswa(): void
    {
        $this->tulisCsv([
            $this->barisLengkap(['nim' => '4.33.23.0.01', 'nama' => 'BUDI SANTOSO']),
            $this->barisLengkap(['nim' => '4.33.23.1.09', 'nama' => 'BUDI RAHARJO']),
        ]);
        $this->impor(['--email-kampus' => 'mhs.polines.ac.id'])->assertSuccessful();

        $email = User::whereIn('username', ['4.33.23.0.01', '4.33.23.1.09'])->pluck('email');

        $this->assertCount(2, $email->unique(), 'Dua mahasiswa bernama depan sama berbagi alamat email.');
    }

    /** Jalan keluarnya: mahasiswa mengisi email aslinya sendiri lewat Profil. */
    public function test_mahasiswa_bisa_mengganti_emailnya_sendiri(): void
    {
        $this->tulisCsv([$this->barisLengkap()]);
        $this->impor(['--password' => 'rahasia123'])->assertSuccessful();

        $user = User::where('username', '4.33.23.0.01')->firstOrFail();

        $this->actingAs($user)->put('/mahasiswa/profile', [
            'name'  => $user->name,
            'email' => 'adriansyah.asli@gmail.com',
        ]);

        $this->assertSame('adriansyah.asli@gmail.com', $user->fresh()->email);
    }

    public function test_mahasiswa_bisa_masuk_dengan_sandi_yang_dicetak(): void
    {
        $this->tulisCsv([$this->barisLengkap()]);
        $this->impor(['--password' => 'rahasia123'])->assertSuccessful();

        $this->post('/login', ['username' => '4.33.23.0.01', 'password' => 'rahasia123'])
            ->assertRedirect();

        $this->assertAuthenticated();
    }

    /** Dosen yang sudah ada dipakai ulang meski format gelarnya berbeda. */
    public function test_dosen_yang_sudah_ada_tidak_diduplikasi(): void
    {
        // Dihitung khusus peran 'lecturer': baris yang sama juga melahirkan akun
        // pembimbing industri, yang memang seharusnya bertambah.
        $hitungDosen = fn () => Lecturer::whereHas('user', fn ($q) => $q->where('role', 'lecturer'))->count();
        $adaSebelum  = $hitungDosen();

        $this->tulisCsv([
            $this->barisLengkap(['nim' => '4.33.23.0.01', 'dospem' => 'DR. DOSEN SATU, S.KOM., M.KOM.']),
        ]);
        $this->impor()->assertSuccessful();

        $this->assertSame($adaSebelum, $hitungDosen(),
            'Dosen "Dosen Satu" dibuat ulang padahal sudah ada dengan gelar berbeda.');

        $student = Student::whereHas('user', fn ($q) => $q->where('username', '4.33.23.0.01'))->firstOrFail();
        $this->assertSame($this->userByUsername('dosen1')->lecturer->id, $student->lecturer_id);
    }

    public function test_dosen_yang_belum_ada_dibuat(): void
    {
        $this->tulisCsv([$this->barisLengkap(['dospem' => 'MUTTABIK FATHUL LATHIEF, S.KOM., M.ENG.'])]);
        $this->impor()->assertSuccessful();

        $dosen = User::where('name', 'MUTTABIK FATHUL LATHIEF, S.KOM., M.ENG.')->firstOrFail();

        $this->assertSame('lecturer', $dosen->role);
        $this->assertNotNull($dosen->lecturer);
    }

    /**
     * Prodi dosen TIDAK boleh disimpulkan dari prodi mahasiswanya.
     *
     * Pembimbingan lintas prodi hal biasa — dosen D4 rutin membimbing mahasiswa
     * D3. Daftar plotting tak memuat prodi dosen, jadi menebaknya berarti
     * menuliskan data yang belum tentu benar ke halaman Data Dosen.
     */
    public function test_prodi_dosen_tidak_ditebak_dari_prodi_mahasiswa(): void
    {
        $this->tulisCsv([$this->barisLengkap(['dospem' => 'TAHAN PRAHARA, S.KOM., M.KOM.'])]);
        $this->impor(['--prodi' => 'Teknologi Rekayasa Komputer'])->assertSuccessful();

        $dosen = User::where('name', 'TAHAN PRAHARA, S.KOM., M.KOM.')->firstOrFail();

        $this->assertNull($dosen->lecturer->study_program,
            'Prodi dosen ditebak dari prodi mahasiswanya — padahal bisa berbeda.');

        // Mahasiswanya sendiri tetap memakai prodi dari opsi.
        $this->assertSame(
            'Teknologi Rekayasa Komputer',
            Student::whereHas('user', fn ($q) => $q->where('username', '4.33.23.0.01'))->firstOrFail()->study_program
        );
    }

    /**
     * Sandi pembimbing diacak di dalam perintah. Kalau tak ikut dicetak,
     * akunnya ada tapi tak seorang pun bisa masuk — dan satu-satunya jalan
     * keluar tinggal reset satu per satu lewat portal Kaprodi.
     */
    public function test_kredensial_pembimbing_baru_ikut_dicetak(): void
    {
        $this->tulisCsv([$this->barisLengkap([
            'dospem'              => 'TAHAN PRAHARA, S.KOM., M.KOM.',
            'pembimbing_industri' => 'Diaz Rizka Wibowo',
        ])]);

        $this->impor()
            ->expectsOutputToContain('TAHAN PRAHARA, S.KOM., M.KOM.')
            ->expectsOutputToContain('Diaz Rizka Wibowo')
            ->assertSuccessful();
    }

    /** Sandi yang dicetak itu benar-benar bisa dipakai masuk. */
    public function test_dosen_baru_bisa_masuk_dengan_sandinya(): void
    {
        $this->tulisCsv([$this->barisLengkap(['dospem' => 'TAHAN PRAHARA, S.KOM., M.KOM.'])]);
        $this->impor(['--password' => 'rahasia123'])->assertSuccessful();

        $dosen = User::where('name', 'TAHAN PRAHARA, S.KOM., M.KOM.')->firstOrFail();

        $this->post('/login', ['username' => $dosen->username, 'password' => 'rahasia123'])
            ->assertRedirect();

        $this->assertAuthenticated();
    }

    /** Nama yang cuma berbagi satu kata bukan orang yang sama. */
    public function test_nama_mirip_tak_tertukar(): void
    {
        $this->tulisCsv([$this->barisLengkap(['dospem' => 'WAHYU SULISTIYO, S.T., M.KOM.'])]);
        $this->impor()->assertSuccessful();

        $baru = User::where('name', 'WAHYU SULISTIYO, S.T., M.KOM.')->first();
        $this->assertNotNull($baru, 'Dosen berbeda dianggap orang yang sama.');
    }

    // ── Data magang ─────────────────────────────────────────────────────────

    public function test_magang_dicatat_lengkap(): void
    {
        $this->tulisCsv([$this->barisLengkap()]);
        $this->impor()->assertSuccessful();

        $student = Student::whereHas('user', fn ($q) => $q->where('username', '4.33.23.0.01'))->firstOrFail();
        $magang  = Internship::where('student_id', $student->id)->firstOrFail();

        $this->assertSame('2026-08-03', $magang->start_date->toDateString());
        $this->assertSame('2026-12-31', $magang->end_date->toDateString());
        $this->assertSame('PT DYNAMIC TALENTA NAVIGATOR', $magang->company->name);
        $this->assertSame('Bagus Kuncoro Aziz', $magang->lecturerIndustry->user->name);
        $this->assertFalse((bool) $magang->is_finished);
    }

    /** Tanggal kosong = tempat magang belum pasti; jangan dikarang. */
    public function test_tanpa_tanggal_magang_tak_dicatat(): void
    {
        $this->tulisCsv([$this->barisLengkap(['mulai' => '', 'selesai' => ''])]);
        $this->impor()->assertSuccessful();

        $student = Student::whereHas('user', fn ($q) => $q->where('username', '4.33.23.0.01'))->firstOrFail();

        $this->assertSame(0, Internship::where('student_id', $student->id)->count());
        $this->assertNotNull($student->lecturer_id, 'Dospem tetap harus terplot walau magangnya belum ada.');
    }

    /** Pembimbing industri banyak yang belum diterima namanya dari perusahaan. */
    public function test_magang_tetap_dicatat_tanpa_pembimbing_industri(): void
    {
        $this->tulisCsv([$this->barisLengkap(['pembimbing_industri' => ''])]);
        $this->impor()->assertSuccessful();

        $student = Student::whereHas('user', fn ($q) => $q->where('username', '4.33.23.0.01'))->firstOrFail();
        $magang  = Internship::where('student_id', $student->id)->firstOrFail();

        $this->assertNull($magang->lecturer_industry_id);
        $this->assertNotNull($magang->company_id);
    }

    public function test_perusahaan_yang_sama_tak_diduplikasi(): void
    {
        $this->tulisCsv([
            $this->barisLengkap(['nim' => '4.33.23.0.01']),
            $this->barisLengkap(['nim' => '4.33.23.0.02', 'nama' => 'AGUNG HADI']),
        ]);
        $this->impor()->assertSuccessful();

        $this->assertSame(1, Company::where('name', 'PT DYNAMIC TALENTA NAVIGATOR')->count());
    }

    // ── Idempoten ───────────────────────────────────────────────────────────

    public function test_dijalankan_ulang_tak_menggandakan(): void
    {
        $this->tulisCsv([$this->barisLengkap()]);

        $this->impor()->assertSuccessful();
        $this->impor()->assertSuccessful();

        $this->assertSame(1, User::where('username', '4.33.23.0.01')->count());
        $this->assertSame(1, Company::where('name', 'PT DYNAMIC TALENTA NAVIGATOR')->count());

        $student = Student::whereHas('user', fn ($q) => $q->where('username', '4.33.23.0.01'))->firstOrFail();
        $this->assertSame(1, Internship::where('student_id', $student->id)->count());
    }

    /** Sandi yang sudah dibagikan tak boleh berubah saat impor diulang. */
    public function test_dijalankan_ulang_tak_mengubah_sandi(): void
    {
        $this->tulisCsv([$this->barisLengkap()]);
        $this->impor(['--password' => 'rahasia123'])->assertSuccessful();

        $this->impor(['--password' => 'sandibaru456'])->assertSuccessful();

        $this->post('/login', ['username' => '4.33.23.0.01', 'password' => 'rahasia123'])->assertRedirect();
        $this->assertAuthenticated();
    }

    // ── Perlindungan ────────────────────────────────────────────────────────

    public function test_berkas_tak_ada_ditolak(): void
    {
        $this->artisan('simama:impor-peserta', ['--berkas' => 'tidak/ada.csv'])->assertFailed();
    }

    public function test_kolom_kurang_ditolak(): void
    {
        file_put_contents($this->berkas, "nim,nama\n4.33.23.0.01,Uji\n");

        $this->impor()->assertFailed();
        $this->assertNull(User::where('username', '4.33.23.0.01')->first());
    }

    public function test_pratinjau_tak_menulis_apa_pun(): void
    {
        $this->tulisCsv([$this->barisLengkap()]);

        $this->impor(['--pratinjau' => true])->assertSuccessful();

        $this->assertNull(User::where('username', '4.33.23.0.01')->first());
        $this->assertSame(0, Company::where('name', 'PT DYNAMIC TALENTA NAVIGATOR')->count());
    }
}
