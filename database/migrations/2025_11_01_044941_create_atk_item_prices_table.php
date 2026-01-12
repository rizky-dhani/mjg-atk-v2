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
        Schema::create('atk_item_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('category_id');
            $table->integer('unit_price');
            $table->date('effective_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('atk_items')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('atk_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk_item_prices');
    }
};
