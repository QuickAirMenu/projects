<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createIndexIfMissing('tasks', 'tasks', ['parent_task_id', 'due_date'], 'idx_tasks_parent_due');
        $this->createIndexIfMissing('tasks', 'tasks', ['status_id', 'due_date'], 'idx_tasks_status_due');
        $this->createIndexIfMissing('tasks', 'tasks', ['assigned_to'], 'idx_tasks_assigned_to');

        $this->createIndexIfMissing('projects', 'projects', ['is_archived'], 'idx_projects_archived');
        $this->createIndexIfMissing('projects', 'projects', ['deadline'], 'idx_projects_deadline');
        $this->createIndexIfMissing('projects', 'projects', ['payment_status'], 'idx_projects_paystatus');

        $this->createIndexIfMissing('invoices', 'invoices', ['payment_status', 'due_date'], 'idx_invoices_status_due');
        $this->createIndexIfMissing('invoices', 'invoices', ['client_id'], 'idx_invoices_client_id');

        $this->createIndexIfMissing('time_logs', 'time_logs', ['user_id', 'logged_date'], 'idx_timelogs_user_date');

        $this->createIndexIfMissing('clients', 'clients', ['email'], 'idx_clients_email');
        $this->createIndexIfMissing('clients', 'clients', ['status'], 'idx_clients_status');

        if (Schema::hasTable('quote_views')) {
            $this->createIndexIfMissing('quote_views', 'quote_views', ['quote_id', 'viewed_at'], 'idx_quoteviews_quote_viewed');
        }
    }

    public function down(): void
    {
        foreach ([
            ['tasks', 'idx_tasks_parent_due'],
            ['tasks', 'idx_tasks_status_due'],
            ['tasks', 'idx_tasks_assigned_to'],
            ['projects', 'idx_projects_archived'],
            ['projects', 'idx_projects_deadline'],
            ['projects', 'idx_projects_paystatus'],
            ['invoices', 'idx_invoices_status_due'],
            ['invoices', 'idx_invoices_client_id'],
            ['time_logs', 'idx_timelogs_user_date'],
            ['clients', 'idx_clients_email'],
            ['clients', 'idx_clients_status'],
            ['quote_views', 'idx_quoteviews_quote_viewed'],
        ] as [$table, $index]) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $b) use ($index) {
                    $b->dropIndex($index);
                });
            }
        }
    }

    private function createIndexIfMissing(string $table, string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            $exists = DB::select("SHOW INDEX FROM `{$tableName}` WHERE Key_name = ?", [$indexName]);
            if (! empty($exists)) {
                return;
            }
        }

        try {
            Schema::table($table, function (Blueprint $b) use ($columns, $indexName) {
                $b->index($columns, $indexName);
            });
        } catch (Throwable $e) {
            // Index may already exist on non-MySQL drivers
        }
    }
};
