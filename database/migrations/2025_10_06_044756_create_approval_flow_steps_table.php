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
        Schema::create('approval_flow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')->constrained('approval_flows')->onDelete('cascade');
            $table->string('step_name');
            $table->integer('step_number');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade'); // Using spatie roles
            $table->foreignId('division_id')->nullable()->constrained('user_divisions')->onDelete('set null');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_flow_steps');
    }
};
