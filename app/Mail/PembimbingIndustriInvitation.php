<?php

namespace App\Mail;

use App\Models\InvitationToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PembimbingIndustriInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public string $activationUrl;
    public string $picName;
    public string $studentName;

    public function __construct(InvitationToken $token, string $studentName)
    {
        $this->activationUrl = url('/aktivasi/' . $token->token);
        $this->picName       = $token->user->name;
        $this->studentName   = $studentName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Aktivasi Akun Pembimbing Industri - SIMAMA Polines');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.pembimbing-aktivasi');
    }
}
