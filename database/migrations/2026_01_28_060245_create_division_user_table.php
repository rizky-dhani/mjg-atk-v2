<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('division_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained('user_divisions')->cascadeOnDelete();
            $table->primary(['user_id', 'division_id']);
        });

        // Migrate existing data
        $users = DB::table('users')->whereNotNull('division_id')->get();
        foreach ($users as $user) {
            DB::table('division_user')->insert([
                'user_id' => $user->id,
                'division_id' => $user->division_id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('division_user');
    }
};
