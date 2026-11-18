<?php

namespace App\Console\Commands;

use App\Services\InvoiceReminderService;
use Illuminate\Console\Command;

class SendDueInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders {--days= : Minimum days between reminders for the same invoice}';

    protected $description = 'Send automated reminders for overdue invoices (respecting a per-invoice interval)';

    public function handle(InvoiceReminderService $service): int
    {
        try {
            [$sent, $skipped, $failed] = $service->sendDue(
                $this->option('days') !== null ? (int) $this->option('days') : null
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Sent: {$sent}, Skipped: {$skipped}, Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
