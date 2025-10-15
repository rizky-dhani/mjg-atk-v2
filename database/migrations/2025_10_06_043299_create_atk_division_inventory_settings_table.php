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
        Schema::create('atk_division_inventory_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('user_divisions')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('atk_items')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('atk_categories')->onDelete('cascade');
            $table->integer('max_limit')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk_division_inventory_settings');
    }
};
