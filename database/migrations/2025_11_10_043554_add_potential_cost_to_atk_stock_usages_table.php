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
        Schema::table('atk_stock_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('potential_cost')->default(0)->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atk_stock_usages', function (Blueprint $table) {
            $table->dropColumn('potential_cost');
        });
    }
};
