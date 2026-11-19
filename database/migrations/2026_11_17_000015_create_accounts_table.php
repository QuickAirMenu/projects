<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('type', 20)->default('bank');
            $table->char('currency', 3)->default('SAR');
            $table->string('bank_name', 120)->nullable();
            $table->string('account_number', 60)->nullable();
            $table->string('iban', 60)->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('exchange_rate_to_base', 12, 6)->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('direction', 10);
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3);
            $table->decimal('exchange_rate', 12, 6)->default(1);
            $table->decimal('base_amount', 14, 2);
            $table->string('category', 40)->default('other');
            $table->string('description', 300)->nullable();
            $table->string('reference_type', 120)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->uuid('transfer_group')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['account_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('transfer_group');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('payment_method')->constrained('accounts')->nullOnDelete();
            $table->decimal('exchange_rate', 12, 6)->default(1)->after('account_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('payment_method')->constrained('accounts')->nullOnDelete();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->char('cost_currency', 3)->nullable()->after('cost_paid_at');
            $table->foreignId('paid_account_id')->nullable()->after('cost_currency')->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['paid_account_id']);
            $table->dropColumn(['cost_currency', 'paid_account_id']);
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn(['account_id', 'exchange_rate']);
        });
        Schema::dropIfExists('account_transactions');
        Schema::dropIfExists('accounts');
    }
};
