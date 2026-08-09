<?php

namespace Tests\Feature;

use App\Models\Internship;
use App\Models\Lecturer;
use Tests\FeatureTestCase;

/**
 * Dosen pembimbing tak boleh diganti setelah magang ditandai selesai.
 *
 * Magang yang sudah ditutup adalah jejak akademik yang sah, dan nama dosen
 * pembimbingnya ikut tercetak di lembar nilai serta berita acara seminar.
 * Menggantinya sesudah itu membuat dokumen yang sudah ditandatangani menunjuk
 * orang yang berbeda dari yang tercatat sekarang — tanpa jejak bahwa pernah ada
 * pergantian.
 *
 * Dulu tombol "Ganti Dosen" tetap hidup meski lencana "Selesai" tercetak tepat
 * di sebelahnya, dan endpoint-nya menerima kiriman tanpa pemeriksaan apa pun.
 *
 * Bukan jalan buntu: Kaprodi bisa membuka kembali status selesainya lewat
 * halaman detail mahasiswa, lalu mengganti dospem seperti biasa.
 */
class GantiDospemTerkunciSetelahSelesaiTest extends FeatureTestCase
{
    private function magang(bool $selesai): Internship
    {
        $internship = Internship::firstOrFail();
        $internship->update(['is_finished' => $selesai, 'finish_requested' => $selesai]);

        return $internship->fresh();
    }

    /** Dosen lain untuk dijadikan sasaran pergantian. */
    private function dosenLain(Internship $internship): Lecturer
    {
        return Lecturer::where('id', '!=', $internship->lecturer_id)->firstOrFail();
    }

    public function test_endpoint_menolak_ganti_dospem_saat_magang_selesai(): void
    {
        $internship = $this->magang(true);
        $student    = $internship->student;
        $semula     = $student->lecturer_id;
        $lain       = $this->dosenLain($internship);

        $this->actingAs($this->userByUsername('kaprodi'))
            ->from(route('kaprodi.mahasiswa.index'))
            ->post(route('kaprodi.mahasiswa.assign', $student), ['lecturer_id' => $lain->id])
            ->assertRedirect(route('kaprodi.mahasiswa.index'))
            ->assertSessionHas('error');

        $this->assertSame($semula, $student->fresh()->lecturer_id,
            'Dospem tetap berganti meski magangnya sudah selesai — tombol yang dimatikan '
            . 'tidak menutup endpoint-nya.');
        $this->assertSame($internship->lecturer_id, $internship->fresh()->lecturer_id,
            'Dospem di data magang ikut berganti padahal magangnya sudah ditutup.');
    }

    /** Selama magang masih berjalan, pergantian tetap boleh. */
    public function test_masih_boleh_ganti_dospem_saat_magang_berjalan(): void
    {
        $internship = $this->magang(false);
        $student    = $internship->student;
        $lain       = $this->dosenLain($internship);

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post(route('kaprodi.mahasiswa.assign', $student), ['lecturer_id' => $lain->id])
            ->assertSessionHas('success');

        $this->assertSame($lain->id, $student->fresh()->lecturer_id,
            'Pergantian dospem ikut terblokir padahal magangnya masih berjalan — '
            . 'penjaganya terlalu luas.');
        $this->assertSame($lain->id, $internship->fresh()->lecturer_id,
            'Dospem di data magang tak ikut disinkronkan.');
    }

    /**
     * Daftar mahasiswa Kaprodi terbuka pada tab "Menunggu" dan menyaring ke
     * periode yang berjalan, sedangkan mahasiswa fixture berstatus aktif dan
     * belum punya periode — tanpa kedua parameter ini ia tak muncul sama sekali
     * dan asersinya lolos tanpa menguji apa pun.
     *
     * Ini nyata terjadi saat tes ini pertama ditulis: penanda `openAssign(`
     * yang terbaca ternyata berasal dari definisi fungsinya di JavaScript,
     * bukan dari baris mahasiswanya. Karena itu tiap tes di bawah memastikan
     * lebih dulu bahwa nama mahasiswanya memang ada di halaman.
     */
    private function daftarKaprodi(string $tab): string
    {
        return $this->actingAs($this->userByUsername('kaprodi'))
            ->get(route('kaprodi.mahasiswa.index', ['status' => $tab, 'periode' => 'semua']))
            ->assertOk()
            ->getContent();
    }

    public function test_tombol_mati_saat_magang_selesai(): void
    {
        $student = $this->magang(true)->student;
        // Tab "aktif" menyaring is_finished = false; yang selesai ada di tab "selesai".
        $html    = $this->daftarKaprodi('selesai');

        $this->assertStringContainsString($student->user->name, $html,
            'Mahasiswanya tak terdaftar di halaman, sehingga tes ini tak menguji apa pun.');

        $this->assertMatchesRegularExpression('/<button[^>]*disabled[^>]*>\s*Ganti Dosen/s', $html,
            'Tombol Ganti Dosen masih hidup padahal magangnya sudah selesai.');

        $this->assertStringNotContainsString("openAssign({$student->id},", $html,
            'Baris mahasiswa ini masih memanggil openAssign — tombolnya belum benar-benar mati.');
    }

    public function test_tombol_hidup_saat_magang_berjalan(): void
    {
        $student = $this->magang(false)->student;
        $html    = $this->daftarKaprodi('aktif');

        $this->assertStringContainsString($student->user->name, $html,
            'Mahasiswanya tak terdaftar di halaman, sehingga tes ini tak menguji apa pun.');

        $this->assertStringContainsString("openAssign({$student->id},", $html,
            'Tombol Ganti Dosen ikut mati padahal magangnya masih berjalan.');
    }
}
