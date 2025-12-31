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
        Schema::create('atk_floating_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('atk_items')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('atk_categories')->onDelete('cascade');
            $table->integer('current_stock')->default(0);
            $table->integer('moving_average_cost')->default(0);
            $table->timestamps();

            // Ensure an item only appears once in the floating stock pool
            $table->unique('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk_floating_stocks');
    }
};