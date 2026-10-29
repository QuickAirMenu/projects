<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\Setting;
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
        $subject = app()->isLocale('ar')
            ? "تم تسليم المشروع #{$this->project->project_number}"
            : "Project Delivered #{$this->project->project_number}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $this->project->load(['client', 'manager', 'attachments', 'invoices', 'quotes']);

        $showInvoices = Setting::where('key', 'portal_show_invoices')->value('value') === '1';
        $hasPortalAccount = $this->project->client?->portalAccount?->email_verified_at !== null;

        $settings = Setting::whereIn('key', [
            'company_name', 'company_name_ar',
            'company_email', 'company_phone', 'company_address',
        ])->pluck('value', 'key')->toArray();

        $settings['company_logo_data'] = pdf_logo_data();
        $settings['company_name_display'] = app()->isLocale('ar')
            ? ($settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name'))
            : ($settings['company_name'] ?? config('app.name'));

        return new Content(
            view: 'emails.project-handoff',
            with: [
                'project' => $this->project,
                'showInvoices' => $showInvoices,
                'hasPortalAccount' => $hasPortalAccount,
                'settings' => $settings,
            ]
        );
    }
}
