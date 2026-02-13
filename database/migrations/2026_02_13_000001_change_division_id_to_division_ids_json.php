<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_flows', function (Blueprint $table) {
            // Drop the existing foreign key first
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
        });

        Schema::table('approval_flows', function (Blueprint $table) {
            // Add new JSON column for multiple divisions
            $table->json('division_ids')->nullable()->after('model_type');
        });
    }

    public function down(): void
    {
        Schema::table('approval_flows', function (Blueprint $table) {
            $table->dropColumn('division_ids');
        });

        Schema::table('approval_flows', function (Blueprint $table) {
            $table->foreignId('division_id')
                ->nullable()
                ->constrained('user_divisions')
                ->onDelete('cascade')
                ->after('model_type');
        });
    }
};
