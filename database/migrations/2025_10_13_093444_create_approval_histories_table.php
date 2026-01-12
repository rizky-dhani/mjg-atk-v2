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
        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable'); // Polymorphic relationship to track which model was approved (e.g., AtkStockRequest, AtkStockUsage)
            $table->string('document_id')->nullable(); // Document identifier for easy tracking on views
            $table->foreignId('approval_id')->constrained('approvals')->onDelete('cascade'); // Link to the main approval record
            $table->foreignId('step_id')->nullable()->constrained('approval_flow_steps')->onDelete('set null'); // Link to the specific approval step
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // User who performed the action
            $table->enum('action', ['approved', 'rejected', 'pending', 'submitted', 'returned'])->default('pending'); // Type of action
            $table->text('rejection_reason')->nullable(); // Reason for rejection if action is 'rejected'
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamp('performed_at')->useCurrent(); // When the action was performed
            $table->json('metadata')->nullable(); // Additional metadata like step number, etc.
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['approvable_type']);
            $table->index(['document_id']);
            $table->index(['approval_id']);
            $table->index(['user_id']);
            $table->index(['action']);
            $table->index(['performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
    }
};
