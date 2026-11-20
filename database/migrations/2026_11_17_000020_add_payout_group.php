<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->uuid('payout_group')->nullable()->after('transfer_group');
            $table->index('payout_group');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->uuid('payout_group')->nullable()->after('paid_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropIndex(['payout_group']);
            $table->dropColumn('payout_group');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('payout_group');
        });
    }
};
