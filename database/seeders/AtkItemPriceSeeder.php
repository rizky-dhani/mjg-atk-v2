<?php

namespace Database\Seeders;

use App\Models\AtkItem;
use App\Models\AtkItemPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AtkItemPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all ATK items
        $items = AtkItem::all();
        
        if ($items->isEmpty()) {
            echo "No ATK items found. Please run AtkItemSeeder first.\n";
            return;
        }
        
        $prices = [];
        $now = now();
        
        foreach ($items as $item) {
            // Generate price between 1000 and 10000 with 500 steps (1000, 1500, 2000, ..., 9500, 10000)
            $randomStep = rand(2, 20); // Random step from 2 to 20 (since 2*500=1000, 20*500=10000)
            $price = $randomStep * 500; // This will give us values from 1000 to 10000 in 500 increments

            $prices[] = [
                'item_id' => $item->id,
                'category_id' => $item->category_id,
                'unit_price' => $price,
                'effective_date' => $now->addMonths(3), // 3 months ahead
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        // Insert all prices
        AtkItemPrice::insert($prices);
        
        echo "Generated prices for " . count($prices) . " ATK items.\n";
    }
}