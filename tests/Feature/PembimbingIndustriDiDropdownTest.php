<?php

namespace Tests\Feature;

use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\FeatureTestCase;

/**
 * Pembimbing industri yang baru dibuat Kaprodi tak muncul di dropdown pada form
 * "Ajukan Magang" mahasiswa.
 *
 * Penyebabnya daftar itu dibangun dari tabel internships — hanya pembimbing
 * yang SUDAH menempel di suatu magang yang ikut terdaftar. Akun yang baru
 * dibuatkan Kaprodi belum menangani magang siapa pun, jadi tak pernah muncul,
 * dan mahasiswa terpaksa mengisi ulang data pembimbing yang sebenarnya sudah
 * ada di sistem — menghasilkan akun ganda untuk orang yang sama.
 */
class PembimbingIndustriDiDropdownTest extends FeatureTestCase
{
    private function buatPembimbingIndustri(string $nama, string $username): Lecturer
    {
        $user = User::create([
            'name'         => $nama,
            'username'     => $username,
            'email'        => $username . '@simama.local',
            'password'     => Hash::make('SandiUji8'),
            'role'         => 'lecturer_industry',
            'is_activated' => true,
        ]);

        return Lecturer::create(['user_id' => $user->id]);
    }

    /** Inti bugnya: akun baru, belum pernah menangani magang. */
    public function test_pembimbing_baru_buatan_kaprodi_muncul_di_dropdown(): void
    {
        $this->buatPembimbingIndustri('Pak Andi Wijaya', 'andi-industri');

        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get('/mahasiswa/ajukan-magang')
            ->assertOk()
            ->assertSee('Pak Andi Wijaya');
    }

    /** Dibuat lewat menu Kaprodi yang sebenarnya, bukan langsung ke basis data. */
    public function test_dibuat_lewat_menu_kaprodi_lalu_langsung_terpilih(): void
    {
        $this->actingAs($this->userByUsername('kaprodi'))
            ->post('/kaprodi/dosen', [
                'tab'      => 'industri',
                'name'     => 'Ibu Sri Rahayu',
                'username' => 'sri-industri',
                'email'    => 'sri@perusahaan.test',
                'password' => 'SandiUji8',
            ])->assertSessionHasNoErrors();

        $lecturer = Lecturer::whereHas('user', fn ($u) => $u->where('username', 'sri-industri'))
            ->firstOrFail();

        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get('/mahasiswa/ajukan-magang')
            ->assertOk()
            ->assertSee('Ibu Sri Rahayu')
            ->assertSee('value="' . $lecturer->id . '"', false);
    }

    /** Yang sudah menangani magang tetap tampil beserta nama perusahaannya. */
    public function test_pembimbing_lama_tetap_tampil_dengan_perusahaannya(): void
    {
        $industri = $this->userByUsername('industri1');
        $company  = $this->userByUsername('3.34.23.2.01')->student
            ->internships()->latest('id')->first()->company;

        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get('/mahasiswa/ajukan-magang')
            ->assertOk()
            ->assertSee($industri->name)
            ->assertSee($company->name);
    }

    /** Batas: dosen kampus bukan pembimbing industri, jangan ikut terdaftar. */
    public function test_dosen_kampus_tidak_masuk_dropdown(): void
    {
        $halaman = $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get('/mahasiswa/ajukan-magang')->assertOk();

        $dosen = $this->userByUsername('dosen1');

        // Dosen kampus tampil di kartu "Dosen Pembimbing", jadi yang diperiksa
        // adalah id-nya sebagai pilihan lecturer_industry_id, bukan namanya.
        $halaman->assertDontSee('value="' . $dosen->lecturer->id . '"', false);
    }

    /** Pilihan itu benar-benar bisa dipakai mengajukan magang. */
    public function test_pembimbing_terpilih_tersimpan_pada_pengajuan(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $lecturer = $this->buatPembimbingIndustri('Pak Budi Hartono', 'budi-industri');
        $mhs      = $this->userByUsername('3.34.23.2.02');

        $this->actingAs($mhs)->post('/mahasiswa/ajukan-magang', [
            'company_name'         => 'PT Uji Dropdown',
            'lecturer_industry_id' => $lecturer->id,
            'position'             => 'Developer',
            'start_date'           => now()->addMonth()->toDateString(),
            'end_date'             => now()->addMonths(4)->toDateString(),
            'proof_file'           => \Illuminate\Http\UploadedFile::fake()
                ->create('bukti.pdf', 50, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $pengajuan = \App\Models\CompanyRequest::where('company_name', 'PT Uji Dropdown')->firstOrFail();

        $this->assertSame($lecturer->id, $pengajuan->lecturer_industry_id,
            'Pembimbing yang dipilih tidak tersimpan pada pengajuan.');
        $this->assertSame('Pak Budi Hartono', $pengajuan->pic_name,
            'Nama pembimbing tidak ikut tersalin dari akun yang dipilih.');
    }
}
