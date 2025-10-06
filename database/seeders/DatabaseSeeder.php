<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class
        ]);

        // Create a default super admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@medquest.co.id',
            'password' => Hash::make('Superadmin2025!'),
        ]);
        $superAdmin->assignRole('Super Admin');
    }
}
