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
        Schema::create('atk_budgetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('user_divisions')->onDelete('cascade');
            $table->integer('budget_amount')->default(0);
            $table->integer('used_amount')->default(0);
            $table->integer('remaining_amount')->default(0);
            $table->year('fiscal_year');
            $table->timestamps();
            
            $table->unique(['division_id', 'fiscal_year']); // Each division can have only one budget per fiscal year
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk_budgetings');
    }
};
