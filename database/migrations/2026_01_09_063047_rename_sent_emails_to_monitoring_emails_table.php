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
        Schema::rename('sent_emails', 'monitoring_emails');

        Schema::table('monitoring_emails', function (Blueprint $table) {
            $table->string('action_type')->nullable()->after('subject'); // e.g., 'Approve', 'Reject'
            $table->foreignId('action_by_id')->nullable()->after('action_type')->constrained('users');
            $table->timestamp('action_at')->nullable()->after('action_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitoring_emails', function (Blueprint $table) {
            $table->dropForeign(['action_by_id']);
            $table->dropColumn(['action_type', 'action_by_id', 'action_at']);
        });

        Schema::rename('monitoring_emails', 'sent_emails');
    }
};