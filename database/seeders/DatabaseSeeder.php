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
            AtkDivisionStockSettingSeeder::class,
            AtkDivisionStockSeeder::class,
            MarketingMediaCategorySeeder::class,
            MarketingMediaItemSeeder::class,
            MarketingMediaDivisionStockSettingSeeder::class,
            MarketingMediaDivisionStockSeeder::class,
            ApprovalFlowSeeder::class,
        ]);
    }
}
