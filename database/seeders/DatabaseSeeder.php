<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            // AtkItemPriceSeeder::class,
            AtkDivisionStockSettingSeeder::class,
            // AtkDivisionStockSeeder::class,
            ApprovalFlowSeeder::class,
            // MovingAverageCostDemoSeeder::class, // Add this line
        ]);
    }
}
