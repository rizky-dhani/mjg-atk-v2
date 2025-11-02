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
        Schema::create('atk_transfer_stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_stock_id')->constrained('atk_transfer_stocks');
            $table->foreignId('item_id')->constrained('atk_items'); // This will reference atk_items table by default
            $table->foreignId('item_category_id')->constrained('atk_categories'); // This will reference atk_items table by default
            $table->foreignId('source_division_id')->nullable()->constrained('user_divisions');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk_transfer_stock_items');
    }
};
