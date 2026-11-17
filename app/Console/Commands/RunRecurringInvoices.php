<?php

namespace App\Console\Commands;

use App\Services\RecurringInvoiceService;
use Illuminate\Console\Command;

class RunRecurringInvoices extends Command
{
    protected $signature = 'invoices:run-recurring';

    protected $description = 'Generate invoices for all due recurring schedules';

    public function handle(RecurringInvoiceService $service): int
    {
        [$generated, $failed] = $service->processDue();

        $this->info("Generated: {$generated}, Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
