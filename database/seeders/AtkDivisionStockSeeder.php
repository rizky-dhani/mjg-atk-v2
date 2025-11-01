<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AtkDivisionStock;
use App\Models\AtkDivisionStockSetting;
use App\Models\AtkItem;
use App\Models\UserDivision;
use Illuminate\Database\Seeder;

class AtkDivisionStockSeeder extends Seeder
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
                $setting = AtkDivisionStockSetting::where('division_id', $division->id)
                    ->where('item_id', $item->id)
                    ->first();

                $maxLimit = $setting?->max_limit ?? 0;

                // Only seed if max_limit > 0
                if ($maxLimit > 0) {
                    $stock = rand(1, $maxLimit);

                    AtkDivisionStock::updateOrCreate(
                        [
                            'division_id' => $division->id,
                            'item_id' => $item->id,
                            'category_id' => $item->category->id,
                        ],
                        [
                            'current_stock' => $stock,
                        ]
                    );
                }
            }
        }
    }
}
