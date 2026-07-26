<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

class AuthFlowTest extends FeatureTestCase
{
    public function test_login_redirects_per_role(): void
    {
        $this->post('/login', ['username' => 'kaprodi', 'password' => 'password'])
            ->assertRedirect(route('kaprodi.dashboard'));
        $this->post('/logout');

        $this->post('/login', ['username' => 'dosen1', 'password' => 'password'])
            ->assertRedirect(route('dosen.dashboard'));
        $this->post('/logout');

        $this->post('/login', ['username' => '3.34.23.2.01', 'password' => 'password'])
            ->assertRedirect(route('mahasiswa.dashboard'));
    }

    public function test_wrong_password_rejected(): void
    {
        $this->from('/login')->post('/login', ['username' => 'kaprodi', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_pending_student_blocked(): void
    {
        $this->from('/login')->post('/login', ['username' => '3.34.23.2.03', 'password' => 'password'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_throttled_after_too_many_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', ['username' => 'kaprodi', 'password' => 'salah']);
        }
        $this->from('/login')->post('/login', ['username' => 'kaprodi', 'password' => 'salah'])
            ->assertSessionHasErrors('username');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('username'));

        // NIM berbeda (share IP) tidak ikut terkunci.
        $this->from('/login')->post('/login', ['username' => '3.34.23.2.01', 'password' => 'password'])
            ->assertRedirect(route('mahasiswa.dashboard'));
    }
}
