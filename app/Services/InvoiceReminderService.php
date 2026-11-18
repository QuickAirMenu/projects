<?php

namespace App\Services;

use App\Mail\InvoiceReminderMail;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class InvoiceReminderService
{
    public function sendDue(?int $intervalDays = null): array
    {
        $interval = $intervalDays ?? (int) (Setting::query()->where('key', 'reminder_interval_days')->value('value') ?: 7);
        $interval = max(1, $interval);

        app(MailConfigService::class)->applyDynamicMailConfig();

        if (blank(config('mail.mailers.smtp.host'))) {
            throw new \RuntimeException(__('messages.mail_not_configured'));
        }

        Invoice::query()
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('remaining_amount', '>', 0)
            ->update(['payment_status' => 'overdue']);

        $invoices = Invoice::with('client')
            ->whereIn('payment_status', ['unpaid', 'partially_paid', 'overdue'])
            ->where('remaining_amount', '>', 0)
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereHas('client', fn ($q) => $q->whereNotNull('email')->where('email', '!=', ''))
            ->get();

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            $last = $invoice->reminders()->latest('sent_at')->first();

            if ($last && $last->sent_at->gt(now()->subDays($interval))) {
                $skipped++;

                continue;
            }

            try {
                Mail::to($invoice->client->email)->send(new InvoiceReminderMail($invoice));

                $invoice->reminders()->create([
                    'sent_at' => now(),
                    'days_overdue' => max(0, (int) now()->startOfDay()->diffInDays($invoice->due_date->startOfDay())),
                    'channel' => 'email',
                ]);

                $sent++;
            } catch (\Throwable $e) {
                report($e);
                $failed++;
            }
        }

        return [$sent, $skipped, $failed];
    }
}
