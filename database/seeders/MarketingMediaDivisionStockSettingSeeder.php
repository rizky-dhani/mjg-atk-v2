<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MarketingMediaDivisionStockSetting;
use App\Models\MarketingMediaItem;
use App\Models\UserDivision;
use Illuminate\Database\Seeder;

class MarketingMediaDivisionStockSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = UserDivision::where('name', 'LIKE', '%Marketing%')->get();
        $items = MarketingMediaItem::all();

        foreach ($divisions as $division) {
            foreach ($items as $item) {
                MarketingMediaDivisionStockSetting::updateOrCreate(
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