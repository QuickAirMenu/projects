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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->cascadeOnDelete()->comment('For subtasks');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('status_id')->constrained('task_statuses')->cascadeOnDelete();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('estimated_hours', 5, 2)->nullable();
            $table->decimal('actual_hours', 5, 2)->default(0);
            $table->integer('progress_percentage')->default(0)->comment('0-100');
            $table->boolean('is_blocked')->default(false);
            $table->text('blocked_reason')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('project_id');
            $table->index('parent_task_id');
            $table->index('status_id');
            $table->index('assigned_to');
            $table->index('due_date');
            $table->index('is_blocked');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
