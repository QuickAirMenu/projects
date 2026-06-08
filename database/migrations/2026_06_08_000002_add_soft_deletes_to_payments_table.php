<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function ($table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function ($table) {
            $table->dropSoftDeletes();
        });
    }
};
