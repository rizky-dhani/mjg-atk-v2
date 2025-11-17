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
        Schema::table('atk_transfer_stocks', function (Blueprint $table) {
            $table->foreignId('source_division_id')->after('requesting_division_id')->nullable()->constrained('user_divisions');
        });

        // Remove the source_division_id from atk_transfer_stock_items table since we'll use the one from main table
        Schema::table('atk_transfer_stock_items', function (Blueprint $table) {
            $table->dropForeign(['source_division_id']);
            $table->dropColumn('source_division_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atk_transfer_stocks', function (Blueprint $table) {
            $table->dropForeign(['source_division_id']);
            $table->dropColumn('source_division_id');
        });

        // Restore the source_division_id column in atk_transfer_stock_items
        Schema::table('atk_transfer_stock_items', function (Blueprint $table) {
            $table->foreignId('source_division_id')->nullable()->constrained('user_divisions');
        });
    }
};
