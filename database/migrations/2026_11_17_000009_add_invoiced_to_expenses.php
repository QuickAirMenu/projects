<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('invoiced_invoice_id')->nullable()->after('approved_at')->constrained('invoices')->nullOnDelete();
            $table->index('invoiced_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['invoiced_invoice_id']);
            $table->dropColumn('invoiced_invoice_id');
        });
    }
};
