<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectHandoffMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Project $project) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "تم تسليم المشروع #{$this->project->project_number}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.project-handoff', with: ['project' => $this->project]);
    }
}
