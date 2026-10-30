<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('discount_type', 20)->default('fixed')->after('discount_amount');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->string('discount_type', 20)->default('percentage')->after('discount_amount');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('discount_type');
            $table->dropColumn('discount_value');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('discount_type');
            $table->dropColumn('discount_value');
        });
    }
};
