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
            // Set fixed price based on category
            $categoryName = $item->category->name ?? 'Lain-Lain';
            
            // Assign fixed prices based on category
            switch ($categoryName) {
                case 'Kertas':
                    $price = 50000; // Rp 50,000
                    break;
                case 'Odner':
                    $price = 25000; // Rp 25,000
                    break;
                case 'Buku dan Kwitansi':
                    $price = 15000; // Rp 15,000
                    break;
                case 'Pulpen, Pensil, Stabilo':
                    $price = 10000; // Rp 10,000
                    break;
                case 'Binder Clip':
                    $price = 5000; // Rp 5,000
                    break;
                case 'Label T&J':
                    $price = 3000; // Rp 3,000
                    break;
                default:
                    $price = 5000; // Rp 5,000 for 'Lain-Lain' and other categories
                    break;
            }
            
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