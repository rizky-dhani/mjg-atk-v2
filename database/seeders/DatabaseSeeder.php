<?php

namespace Database\Seeders;

use App\Models\AtkDivisionStock;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserDivisionSeeder::class,
            UserSeeder::class,
            AtkCategorySeeder::class,
            AtkItemSeeder::class,
            AtkDivisionInventorySettingSeeder::class,
            AtkDivisionStockSeeder::class,
            ApprovalFlowSeeder::class,
        ]);
    }
}
