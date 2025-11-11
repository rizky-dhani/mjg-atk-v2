<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add a default value by updating all existing records where remaining_amount is NULL
        DB::table('atk_budgetings')
            ->whereNull('remaining_amount')
            ->update(['remaining_amount' => DB::raw('budget_amount - used_amount')]);
        
        // Then modify the column to have a default value
        Schema::table('atk_budgetings', function (Blueprint $table) {
            $table->integer('remaining_amount')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atk_budgetings', function (Blueprint $table) {
            $table->integer('remaining_amount')->change();
        });
    }
};
