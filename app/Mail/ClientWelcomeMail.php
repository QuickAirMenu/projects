<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Client $client, public string $setupUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to Air Menu Projects');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.client-welcome', with: ['client' => $this->client, 'setupUrl' => $this->setupUrl]);
    }
}
