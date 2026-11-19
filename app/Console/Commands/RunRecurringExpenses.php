<?php

namespace App\Console\Commands;

use App\Services\RecurringExpenseService;
use Illuminate\Console\Command;

class RunRecurringExpenses extends Command
{
    protected $signature = 'expenses:run-recurring';

    protected $description = 'Generate pending expenses for all due recurring schedules';

    public function handle(RecurringExpenseService $service): int
    {
        [$generated, $failed] = $service->processDue();

        $this->info("Generated: {$generated}, Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
