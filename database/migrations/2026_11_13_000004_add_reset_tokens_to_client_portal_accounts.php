<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_portal_accounts', function (Blueprint $table) {
            $table->string('reset_token_hash', 64)->nullable()->after('setup_token_expires_at');
            $table->timestamp('reset_token_expires_at')->nullable()->after('reset_token_hash');
            $table->index('reset_token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('client_portal_accounts', function (Blueprint $table) {
            $table->dropColumn(['reset_token_hash', 'reset_token_expires_at']);
        });
    }
};
