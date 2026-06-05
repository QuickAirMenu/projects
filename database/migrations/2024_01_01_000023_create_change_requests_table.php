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
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('requested_by', ['client', 'internal'])->default('client');
            $table->string('title');
            $table->text('description');
            $table->enum('scope_type', ['in_scope', 'out_of_scope'])->default('in_scope');
            $table->text('impact_analysis')->nullable();
            $table->decimal('additional_cost', 12, 2)->default(0);
            $table->decimal('additional_hours', 5, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'implemented'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index('request_number');
            $table->index('project_id');
            $table->index('status');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_requests');
    }
};
