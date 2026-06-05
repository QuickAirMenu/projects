<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_handed_off')->default(false)->after('is_archived');
            $table->timestamp('handed_off_at')->nullable()->after('is_handed_off');
            $table->text('handoff_notes')->nullable()->after('handed_off_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['is_handed_off', 'handed_off_at', 'handoff_notes']);
        });
    }
};
