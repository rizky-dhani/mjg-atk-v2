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
        Schema::table('atk_stock_requests', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published'])->default('draft')->after('request_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atk_stock_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
