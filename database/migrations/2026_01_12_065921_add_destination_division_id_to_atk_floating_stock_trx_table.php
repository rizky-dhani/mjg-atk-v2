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
        Schema::table('atk_floating_stock_trx', function (Blueprint $table) {
            $table->foreignId('destination_division_id')->nullable()->after('source_division_id')->constrained('user_divisions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atk_floating_stock_trx', function (Blueprint $table) {
            $table->dropForeign(['destination_division_id']);
            $table->dropColumn('destination_division_id');
        });
    }
};
