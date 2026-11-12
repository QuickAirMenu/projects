<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Client-uploaded (portal) payments have no internal receiving user.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `payments` MODIFY `received_by` BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        // Irreversible safely; no-op.
    }
};
