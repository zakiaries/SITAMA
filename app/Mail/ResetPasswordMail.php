<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;
    public string $resetUrl;
    public int $expireMinutes;

    public function __construct(User $user, string $resetUrl, int $expireMinutes = 60)
    {
        $this->userName      = $user->name;
        $this->resetUrl      = $resetUrl;
        $this->expireMinutes = $expireMinutes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reset Password Akun SIMAMA');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.reset-password');
    }
}
