<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

class SyncLifetimeValues extends Command
{
    protected $signature = 'clients:sync-lifetime';

    protected $description = 'Recalculate lifetime_value for all clients from their project budgets';

    public function handle(): int
    {
        $clients = Client::all();
        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        foreach ($clients as $client) {
            $client->recalculateLifetimeValue();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated {$clients->count()} clients.");

        return self::SUCCESS;
    }
}
