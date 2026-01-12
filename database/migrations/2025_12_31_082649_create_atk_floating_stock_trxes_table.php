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
        Schema::create('atk_floating_stock_trx', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('atk_items')->onDelete('cascade');
            $table->enum('type', ['in', 'out', 'adjustment', 'transfer']);
            $table->integer('quantity');
            $table->integer('unit_cost')->default(0);
            $table->integer('total_cost')->default(0);
            $table->integer('mac_snapshot')->default(0);
            $table->integer('balance_snapshot');
            $table->nullableMorphs('trx_src'); // Source can be null for manual adjustments
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk_floating_stock_trx');
    }
};
