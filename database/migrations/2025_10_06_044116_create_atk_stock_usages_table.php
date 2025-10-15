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
        Schema::create('atk_stock_usages', function (Blueprint $table) {
            $table->id();
            $table->string('usage_number')->unique();
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('division_id')->constrained('user_divisions')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk_stock_usages');
    }
};
