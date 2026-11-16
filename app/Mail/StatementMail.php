<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client,
        public string $fromDate,
        public string $toDate,
        public string $pdfContent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('messages.statement_email_subject', ['from' => $this->fromDate, 'to' => $this->toDate]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.statement', with: [
            'client' => $this->client,
            'from' => $this->fromDate,
            'to' => $this->toDate,
        ]);
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'statement-'.$this->fromDate.'_'.$this->toDate.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
