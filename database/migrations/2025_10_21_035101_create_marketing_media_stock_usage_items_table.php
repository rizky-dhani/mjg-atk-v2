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
        Schema::create('marketing_media_stock_usage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usage_id')->constrained('marketing_media_stock_usages')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('marketing_media_items')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('marketing_media_categories')->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_media_stock_usage_items');
    }
};
