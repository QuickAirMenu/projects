<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->default('Saudi Arabia');
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'potential', 'inactive', 'vip'])->default('potential');
            $table->string('industry', 100)->nullable();
            $table->string('source', 100)->nullable()->comment('referral, website, social media, etc.');
            $table->text('notes')->nullable();
            $table->decimal('lifetime_value', 12, 2)->default(0);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('assigned_to');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
