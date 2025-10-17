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
        Schema::table('approval_flow_steps', function (Blueprint $table) {
            $table->boolean('allow_resubmission')->default(false)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_flow_steps', function (Blueprint $table) {
            $table->dropColumn('allow_resubmission');
        });
    }
};