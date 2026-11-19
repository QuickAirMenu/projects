<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name', 150);
            $table->string('category', 40);
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->string('frequency', 20)->default('monthly');
            $table->date('next_run_date');
            $table->timestamp('last_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->boolean('billable')->default(false);
            $table->string('payment_method', 30)->nullable();
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'next_run_date']);
            $table->index('project_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('recurring_expense_id')->nullable()->after('invoiced_invoice_id')->constrained('recurring_expenses')->nullOnDelete();
            $table->index('recurring_expense_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['recurring_expense_id']);
            $table->dropColumn('recurring_expense_id');
        });

        Schema::dropIfExists('recurring_expenses');
    }
};
