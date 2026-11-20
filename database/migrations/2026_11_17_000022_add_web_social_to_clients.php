<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('website', 255)->nullable()->after('address');
            $table->string('linkedin', 255)->nullable()->after('website');
            $table->string('twitter', 255)->nullable()->after('linkedin');
            $table->string('instagram', 255)->nullable()->after('twitter');
            $table->string('facebook', 255)->nullable()->after('instagram');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['website', 'linkedin', 'twitter', 'instagram', 'facebook']);
        });
    }
};
