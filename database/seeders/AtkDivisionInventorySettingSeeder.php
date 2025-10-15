<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AtkDivisionInventorySetting;
use App\Models\AtkItem;
use App\Models\UserDivision;
use Illuminate\Database\Seeder;
use App\Models\CompanyDivision;
use App\Models\OfficeStationeryItem;
use App\Models\OfficeStationeryDivisionInventorySetting;

class AtkDivisionInventorySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = UserDivision::all();
        $items = AtkItem::all();

        foreach ($divisions as $division) {
            foreach ($items as $item) {
                AtkDivisionInventorySetting::updateOrCreate(
                    [
                        'division_id' => $division->id,
                        'item_id' => $item->id,
                        'category_id' => $item->category->id,
                    ],
                    [
                        'max_limit' => 50,
                    ]
                );
            }
        }
    }
}