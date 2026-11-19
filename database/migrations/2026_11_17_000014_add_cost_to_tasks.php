<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->decimal('cost_amount', 12, 2)->nullable()->after('actual_hours');
            $table->timestamp('cost_paid_at')->nullable()->after('cost_amount');
            $table->index(['assigned_to', 'cost_paid_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['assigned_to', 'cost_paid_at']);
            $table->dropColumn(['cost_amount', 'cost_paid_at']);
        });
    }
};
