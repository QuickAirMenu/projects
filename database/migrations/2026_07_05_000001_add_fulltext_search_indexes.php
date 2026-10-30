<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Standard B-tree indexes for LIKE queries (used by all drivers)
        Schema::table('projects', function (Blueprint $table) {
            $table->index('name', 'idx_projects_name');
            $table->index('project_number', 'idx_projects_number');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->index('name', 'idx_clients_name');
            $table->index('company_name', 'idx_clients_company_name');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('invoice_number', 'idx_invoices_number');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->index('quote_number', 'idx_quotes_number');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index('title', 'idx_tasks_title');
        });

        // Fulltext indexes only for MySQL
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE projects ADD FULLTEXT ft_projects_search (name, project_number)');
            DB::statement('ALTER TABLE clients ADD FULLTEXT ft_clients_search (name, company_name, email)');
            DB::statement('ALTER TABLE invoices ADD FULLTEXT ft_invoices_search (invoice_number)');
            DB::statement('ALTER TABLE quotes ADD FULLTEXT ft_quotes_search (quote_number)');
            DB::statement('ALTER TABLE tasks ADD FULLTEXT ft_tasks_search (title)');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropIndex('ft_projects_search');
            });
            Schema::table('clients', function (Blueprint $table) {
                $table->dropIndex('ft_clients_search');
            });
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex('ft_invoices_search');
            });
            Schema::table('quotes', function (Blueprint $table) {
                $table->dropIndex('ft_quotes_search');
            });
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex('ft_tasks_search');
            });
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('idx_projects_name');
            $table->dropIndex('idx_projects_number');
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('idx_clients_name');
            $table->dropIndex('idx_clients_company_name');
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_number');
        });
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex('idx_quotes_number');
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('idx_tasks_title');
        });
    }
};
