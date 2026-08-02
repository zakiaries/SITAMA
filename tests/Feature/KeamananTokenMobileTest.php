<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\FeatureTestCase;

/**
 * Token aplikasi HP dulu berlaku selamanya DAN tak dicabut saat kata sandi
 * diganti. Akibatnya mengganti sandi karena curiga akun disusupi sama sekali
 * tak memutus akses lewat aplikasi — pertahanan yang paling wajar dilakukan
 * orang justru tidak berefek.
 */
class KeamananTokenMobileTest extends FeatureTestCase
{
    private function tokenBaru(User $user, string $nama = 'hp'): string
    {
        return $user->createToken($nama)->plainTextToken;
    }

    public function test_token_dicabut_saat_kaprodi_mereset_sandi_mahasiswa(): void
    {
        $mhs   = $this->userByUsername('3.34.23.2.01');
        $token = $this->tokenBaru($mhs);

        $this->assertSame(1, $mhs->tokens()->count());

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/mahasiswa/{$mhs->student->id}/reset-password", [
                'new_password'              => 'SandiBaru123',
                'new_password_confirmation' => 'SandiBaru123',
            ])->assertSessionHasNoErrors();

        $this->assertSame(0, $mhs->fresh()->tokens()->count(),
            'Token lama masih hidup setelah sandi direset Kaprodi.');
    }

    /**
     * Dipisah dari tes di atas: setelah actingAs() sesi web ikut terautentikasi,
     * sehingga permintaan API terbaca sebagai pengguna itu dan menjawab 403,
     * bukan 401 — jejak sesi, bukan token yang masih hidup.
     */
    public function test_token_yang_dicabut_tak_bisa_dipakai_lagi(): void
    {
        $mhs   = $this->userByUsername('3.34.23.2.01');
        $token = $this->tokenBaru($mhs);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/mahasiswa/dashboard')->assertOk();

        // Sandi diganti dari luar konteks pengguna itu (mis. oleh Kaprodi).
        User::find($mhs->id)->update(['password' => bcrypt('SandiBaru123')]);

        // Guard menyimpan pengguna hasil permintaan pertama; tanpa ini
        // permintaan berikutnya memakai hasil lama, bukan menguji tokennya.
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/mahasiswa/dashboard')
            ->assertUnauthorized();
    }

    public function test_token_dicabut_saat_ganti_sandi_lewat_web(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.01');
        $this->tokenBaru($mhs, 'hp-lama');

        $this->actingAs($mhs)->put('/mahasiswa/profile', [
            'name'                  => $mhs->name,
            'email'                 => $mhs->email,
            'password'              => 'SandiBaru123',
            'password_confirmation' => 'SandiBaru123',
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, $mhs->fresh()->tokens()->count(),
            'Ganti sandi lewat web seharusnya mencabut token aplikasi HP.');
    }

    /**
     * Kecuali satu: token yang SEDANG dipakai saat pemiliknya sendiri mengganti
     * sandi lewat aplikasi. Aplikasi Flutter belum menangani 401, jadi
     * mencabutnya membuat pengguna tersangkut tanpa penjelasan.
     */
    public function test_token_yang_sedang_dipakai_bertahan_saat_ganti_sandi_sendiri(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.01');
        $ini = $this->tokenBaru($mhs, 'hp-ini');
        $this->tokenBaru($mhs, 'hp-lain');

        $this->assertSame(2, $mhs->tokens()->count());

        $this->withHeader('Authorization', 'Bearer ' . $ini)
            ->putJson('/api/mahasiswa/profile', [
                'name'                  => $mhs->name,
                'email'                 => $mhs->email,
                'password'              => 'SandiBaru123',
                'password_confirmation' => 'SandiBaru123',
            ])->assertSuccessful();

        $sisa = $mhs->fresh()->tokens()->pluck('name');
        $this->assertSame(['hp-ini'], $sisa->all(),
            'Token perangkat lain harus dicabut, token yang sedang dipakai bertahan.');

        // Dan token itu memang masih bisa dipakai.
        $this->withHeader('Authorization', 'Bearer ' . $ini)
            ->getJson('/api/mahasiswa/dashboard')->assertOk();
    }

    public function test_token_punya_masa_berlaku(): void
    {
        $this->assertNotNull(config('sanctum.expiration'),
            'Token tanpa masa berlaku artinya HP yang hilang punya akses selamanya.');
    }

    public function test_token_kedaluwarsa_ditolak(): void
    {
        $mhs   = $this->userByUsername('3.34.23.2.01');
        $token = $this->tokenBaru($mhs);

        PersonalAccessToken::query()->update([
            'created_at' => now()->subMinutes((int) config('sanctum.expiration') + 60),
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/mahasiswa/dashboard')
            ->assertUnauthorized();
    }
}
