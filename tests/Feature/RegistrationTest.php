<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\FeatureTestCase;

class RegistrationTest extends FeatureTestCase
{
    private function payload(string $nim): array
    {
        return [
            'name' => 'Calon', 'username' => $nim, 'email' => 'calon_' . md5($nim) . '@x.ac.id',
            'password' => 'password123', 'password_confirmation' => 'password123',
            // Prodi kini daftar tertutup (Student::PRODI), bukan teks bebas.
            // `academic_year` sengaja tak dikirim: ia mengikuti periode berjalan.
            'the_class' => 'TI-1A', 'study_program' => 'Teknik Informatika', 'major' => 'Informatika',
        ];
    }

    public function test_valid_nim_registers_as_pending(): void
    {
        $this->post('/register', $this->payload('4.34.24.1.05'))->assertRedirect(route('login'));
        $user = User::where('username', '4.34.24.1.05')->first();
        $this->assertNotNull($user);
        $this->assertSame('pending', $user->student->status);
    }

    public function test_invalid_nim_rejected(): void
    {
        foreach (['asdf', '33423212', '3.34.23', '3.34.23.2.12x'] as $bad) {
            $this->from('/register')->post('/register', $this->payload($bad))->assertSessionHasErrors('username');
            $this->assertNull(User::where('username', $bad)->first());
        }
    }

    public function test_duplicate_nim_rejected(): void
    {
        $this->from('/register')->post('/register', $this->payload('3.34.23.2.01')) // sudah ada di fixture
            ->assertSessionHasErrors('username');
    }
}
