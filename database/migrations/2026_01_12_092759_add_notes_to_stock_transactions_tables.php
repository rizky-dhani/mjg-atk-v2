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
            $table->text('notes')->nullable()->after('trx_src_type');
        });

        Schema::table('atk_stock_trx', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('trx_src_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atk_floating_stock_trx', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('atk_stock_trx', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
