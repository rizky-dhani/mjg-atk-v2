<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AtkDivisionStockSetting;
use App\Models\AtkItem;
use App\Models\UserDivision;
use Illuminate\Database\Seeder;

class AtkDivisionStockSettingSeeder extends Seeder
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
                AtkDivisionStockSetting::updateOrCreate(
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
