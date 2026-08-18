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
        Schema::create('fulfillment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('atk_stock_requests')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('atk_items')->cascadeOnDelete();
            $table->integer('quantity');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fulfillment_histories');
    }
};
