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
        Schema::create('atk_item_price_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->integer('old_price')->nullable();
            $table->integer('new_price');
            $table->date('effective_date');
            $table->unsignedBigInteger('changed_by')->nullable(); // User ID who made the change
            $table->timestamps();
            
            $table->foreign('item_id')->references('id')->on('atk_items')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk_item_price_histories');
    }
};
