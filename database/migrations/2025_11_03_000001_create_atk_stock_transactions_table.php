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
        Schema::create('atk_stock_trx', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('user_divisions')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('atk_items')->onDelete('cascade');
            $table->enum('type', ['request', 'usage', 'adjustment', 'transfer']);
            $table->integer('quantity');
            $table->integer('unit_cost')->default(0);
            $table->integer('total_cost')->default(0);
            $table->integer('mac_snapshot')->default(0); // Moving average cost at time of transaction
            $table->integer('balance_snapshot'); // Stock balance after transaction
            $table->morphs('trx_src'); // Polymorphic relation to source (AtkStockRequest, AtkStockUsage, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk_stock_trx');
    }
};
