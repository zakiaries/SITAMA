<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Tests\FeatureTestCase;

/**
 * Mahasiswa yang pendaftarannya ditolak Kaprodi tak bisa mendaftar lagi: NIM
 * dan emailnya masih tersimpan, jadi validasi menolak dengan "NIM sudah
 * terdaftar". Akibatnya di lapangan pendaftar memakai NIM lain yang bukan
 * miliknya, hanya supaya bisa masuk.
 *
 * Akun berstatus 'rejected' tak pernah bisa dipakai login, jadi menahannya
 * tidak melindungi apa pun.
 */
class DaftarUlangSetelahDitolakTest extends FeatureTestCase
{
    private array $form = [
        'name'                  => 'Tera Mai',
        'username'              => '3.34.23.2.20',
        'email'                 => 'tera@test.ac.id',
        'password'              => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'the_class'             => 'IK-3A',
        'study_program'         => 'Teknik Informatika',
        'major'                 => 'Teknik Elektro',
        'academic_year'         => '2023/2024',
    ];

    /** Daftar, lalu ditolak Kaprodi. */
    private function daftarLaluDitolak(): Student
    {
        $this->post('/register', $this->form)->assertSessionHasNoErrors();

        $student = User::where('username', $this->form['username'])->firstOrFail()->student;

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/mahasiswa/{$student->id}/reject")
            ->assertSessionHasNoErrors();

        $this->assertSame('rejected', $student->fresh()->status);

        return $student->fresh();
    }

    public function test_bisa_mendaftar_ulang_dengan_nim_dan_email_yang_sama(): void
    {
        $lama = $this->daftarLaluDitolak();

        $this->post('/register', $this->form)->assertSessionHasNoErrors();

        // Akun lama dibersihkan, yang tersisa satu akun baru berstatus pending.
        $this->assertNull(User::find($lama->user_id));
        $this->assertNull(Student::find($lama->id));

        $baru = User::where('username', $this->form['username'])->firstOrFail();
        $this->assertSame('pending', $baru->student->status);
        $this->assertSame(1, User::where('username', $this->form['username'])->count());
    }

    /** Pendaftaran ulang harus bisa dipakai masuk setelah Kaprodi menyetujui. */
    public function test_akun_hasil_daftar_ulang_berfungsi(): void
    {
        $this->daftarLaluDitolak();
        $this->post('/register', $this->form)->assertSessionHasNoErrors();

        $baru = User::where('username', $this->form['username'])->firstOrFail();

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/mahasiswa/{$baru->student->id}/approve")
            ->assertSessionHasNoErrors();

        $this->assertSame('active', $baru->student->fresh()->status);
    }

    /** Kaprodi tetap diberi tahu — pendaftaran ulang tetap perlu ditinjau. */
    public function test_daftar_ulang_tetap_memberi_tahu_kaprodi(): void
    {
        $kaprodi = $this->userByUsername('kaprodi');
        $this->daftarLaluDitolak();

        $sebelum = \App\Models\Notification::where('user_id', $kaprodi->id)
            ->where('category', 'pendaftaran')->count();

        $this->post('/register', $this->form)->assertSessionHasNoErrors();

        $this->assertSame($sebelum + 1, \App\Models\Notification::where('user_id', $kaprodi->id)
            ->where('category', 'pendaftaran')->count());
    }

    // ── Batasnya tidak boleh ikut longgar ──────────────────────────────────

    public function test_nim_akun_aktif_tetap_ditolak(): void
    {
        $aktif = $this->userByUsername('3.34.23.2.01');

        $this->post('/register', array_merge($this->form, [
            'username' => $aktif->username,
        ]))->assertSessionHasErrors('username');

        $this->assertSame(1, User::where('username', $aktif->username)->count());
    }

    public function test_email_akun_aktif_tetap_ditolak(): void
    {
        $aktif = $this->userByUsername('3.34.23.2.01');

        $this->post('/register', array_merge($this->form, [
            'email' => $aktif->email,
        ]))->assertSessionHasErrors('email');
    }

    /** Yang masih menunggu persetujuan bukan "ditolak" — NIM-nya tetap terkunci. */
    public function test_nim_akun_menunggu_tetap_ditolak(): void
    {
        $this->post('/register', $this->form)->assertSessionHasNoErrors();

        $this->post('/register', array_merge($this->form, [
            'email' => 'lain@test.ac.id',
        ]))->assertSessionHasErrors('username');
    }

    /** NIM milik dosen/kaprodi tak boleh bisa direbut lewat celah ini. */
    public function test_username_non_mahasiswa_tetap_ditolak(): void
    {
        $this->post('/register', array_merge($this->form, [
            'username' => $this->userByUsername('dosen1')->username,
        ]))->assertSessionHasErrors('username');
    }
}
