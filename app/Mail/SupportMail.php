<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public $subject,
        public string $message,
    ) {}

    public function envelope(): Envelope
    {
        $settings = Setting::whereIn('key', ['company_name', 'company_name_ar', 'company_logo'])
            ->pluck('value', 'key')
            ->toArray();

        $fromName = $settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name');

        return new Envelope(
            subject: "دعم فني: {$this->subject}",
            replyTo: [$this->senderEmail => $this->senderName],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support',
            with: [
                'senderName' => $this->senderName,
                'senderEmail' => $this->senderEmail,
                'subjectText' => $this->subject,
                'messageText' => $this->message,
            ],
        );
    }
}
