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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('payment_number', 50)->unique();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['bank_transfer', 'cash', 'cheque', 'card', 'online', 'other']);
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_id');
            $table->index('payment_number');
            $table->index('payment_date');
            $table->index('received_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
