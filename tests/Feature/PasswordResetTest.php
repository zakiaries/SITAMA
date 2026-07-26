<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\{Hash, Mail, Password};
use Tests\FeatureTestCase;

class PasswordResetTest extends FeatureTestCase
{
    public function test_request_sends_email_for_known_nim(): void
    {
        Mail::fake();
        $this->post('/lupa-password', ['username' => '3.34.23.2.01'])->assertSessionHas('status');
        Mail::assertSent(ResetPasswordMail::class);
    }

    public function test_request_silent_for_unknown_nim(): void
    {
        Mail::fake();
        $this->post('/lupa-password', ['username' => '9.99.99.9.99'])->assertSessionHas('status');
        Mail::assertNothingSent();
    }

    public function test_reset_with_valid_token_changes_password(): void
    {
        $user  = $this->userByUsername('3.34.23.2.01');
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token, 'email' => $user->email,
            'password' => 'PasswordBaru9', 'password_confirmation' => 'PasswordBaru9',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('PasswordBaru9', $user->fresh()->password));
    }

    public function test_reset_with_invalid_token_rejected(): void
    {
        $user = $this->userByUsername('3.34.23.2.01');
        $this->from('/reset-password/x')->post('/reset-password', [
            'token' => 'salah', 'email' => $user->email,
            'password' => 'PasswordBaru9', 'password_confirmation' => 'PasswordBaru9',
        ])->assertSessionHasErrors('email');
    }
}
