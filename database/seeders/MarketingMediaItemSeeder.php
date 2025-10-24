<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MarketingMediaCategory;
use App\Models\MarketingMediaItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketingMediaItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories to associate with items
        $bannerCategory = MarketingMediaCategory::firstOrCreate(
            ['name' => 'Banner & Signage'],
            ['name' => 'Banner & Signage', 'description' => 'Banners, signs, and promotional displays']
        );

        $printCategory = MarketingMediaCategory::firstOrCreate(
            ['name' => 'Print Materials'],
            ['name' => 'Print Materials', 'description' => 'Brochures, leaflets, business cards, and other printed materials']
        );

        $promoCategory = MarketingMediaCategory::firstOrCreate(
            ['name' => 'Promotional Items'],
            ['name' => 'Promotional Items', 'description' => 'Branded merchandise and giveaways']
        );

        $digitalCategory = MarketingMediaCategory::firstOrCreate(
            ['name' => 'Digital Media'],
            ['name' => 'Digital Media', 'description' => 'Digital marketing materials and assets']
        );

        $displayCategory = MarketingMediaCategory::firstOrCreate(
            ['name' => 'Display Materials'],
            ['name' => 'Display Materials', 'description' => 'Point of sale displays and exhibition materials']
        );

        $kitCategory = MarketingMediaCategory::firstOrCreate(
            ['name' => 'Marketing Kits'],
            ['name' => 'Marketing Kits', 'description' => 'Complete marketing packages and kits']
        );

        $items = [
            // Banner & Signage items
            [
                'name' => 'Banners',
                'unit_of_measure' => 'pieces',
                'category_id' => $bannerCategory->id,
            ],
            [
                'name' => 'Sign Boards',
                'unit_of_measure' => 'pieces',
                'category_id' => $bannerCategory->id,
            ],
            [
                'name' => 'Standing Banner',
                'unit_of_measure' => 'pieces',
                'category_id' => $bannerCategory->id,
            ],
            [
                'name' => 'Roll Up Banner',
                'unit_of_measure' => 'pieces',
                'category_id' => $bannerCategory->id,
            ],
            [
                'name' => 'X Banner',
                'unit_of_measure' => 'pieces',
                'category_id' => $bannerCategory->id,
            ],

            // Print Materials
            [
                'name' => 'Brochures',
                'unit_of_measure' => 'pieces',
                'category_id' => $printCategory->id,
            ],
            [
                'name' => 'Leaflets',
                'unit_of_measure' => 'pieces',
                'category_id' => $printCategory->id,
            ],
            [
                'name' => 'Business Cards',
                'unit_of_measure' => 'pieces',
                'category_id' => $printCategory->id,
            ],
            [
                'name' => 'Flyers',
                'unit_of_measure' => 'pieces',
                'category_id' => $printCategory->id,
            ],
            [
                'name' => 'Catalogs',
                'unit_of_measure' => 'pieces',
                'category_id' => $printCategory->id,
            ],

            // Promotional Items
            [
                'name' => 'Tote Bags',
                'unit_of_measure' => 'pieces',
                'category_id' => $promoCategory->id,
            ],
            [
                'name' => 'T-Shirts',
                'unit_of_measure' => 'pieces',
                'category_id' => $promoCategory->id,
            ],
            [
                'name' => 'Mugs',
                'unit_of_measure' => 'pieces',
                'category_id' => $promoCategory->id,
            ],
            [
                'name' => 'Pens',
                'unit_of_measure' => 'pieces',
                'category_id' => $promoCategory->id,
            ],
            [
                'name' => 'Keychains',
                'unit_of_measure' => 'pieces',
                'category_id' => $promoCategory->id,
            ],

            // Digital Media
            [
                'name' => 'Digital Banners',
                'unit_of_measure' => 'files',
                'category_id' => $digitalCategory->id,
            ],
            [
                'name' => 'Social Media Templates',
                'unit_of_measure' => 'templates',
                'category_id' => $digitalCategory->id,
            ],
            [
                'name' => 'Email Templates',
                'unit_of_measure' => 'templates',
                'category_id' => $digitalCategory->id,
            ],

            // Display Materials
            [
                'name' => 'Counter Displays',
                'unit_of_measure' => 'pieces',
                'category_id' => $displayCategory->id,
            ],
            [
                'name' => 'Shelf Talkers',
                'unit_of_measure' => 'pieces',
                'category_id' => $displayCategory->id,
            ],
            [
                'name' => 'Window Decals',
                'unit_of_measure' => 'pieces',
                'category_id' => $displayCategory->id,
            ],

            // Marketing Kits
            [
                'name' => 'Welcome Kit',
                'unit_of_measure' => 'kits',
                'category_id' => $kitCategory->id,
            ],
            [
                'name' => 'Product Sample Kit',
                'unit_of_measure' => 'kits',
                'category_id' => $kitCategory->id,
            ],
            [
                'name' => 'Event Kit',
                'unit_of_measure' => 'kits',
                'category_id' => $kitCategory->id,
            ],
        ];

        foreach ($items as $item) {
            // Generate slug from the item name
            $slug = Str::slug($item['name']);

            MarketingMediaItem::updateOrCreate(
                ['name' => $item['name'], 'category_id' => $item['category_id']],
                [
                    'name' => $item['name'],
                    'unit_of_measure' => $item['unit_of_measure'],
                    'category_id' => $item['category_id'],
                    'slug' => $slug,
                ]
            );
        }
    }
}
