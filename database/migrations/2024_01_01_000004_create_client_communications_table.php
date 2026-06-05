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
        Schema::create('client_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['call', 'email', 'meeting', 'whatsapp', 'other']);
            $table->string('subject')->nullable();
            $table->text('message');
            $table->dateTime('communication_date');
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('client_id');
            $table->index('user_id');
            $table->index('communication_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_communications');
    }
};
