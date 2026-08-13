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
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->json('divisions')->nullable()->after('role_id');
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->json('divisions')->nullable()->after('model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropColumn('divisions');
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropColumn('divisions');
        });
    }
};
