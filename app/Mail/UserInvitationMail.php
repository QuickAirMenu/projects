<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $setupUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), 'Team'),
            subject: 'دعوة للانضمام إلى الفريق',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-invitation',
            with: ['user' => $this->user, 'setupUrl' => $this->setupUrl]
        );
    }
}
