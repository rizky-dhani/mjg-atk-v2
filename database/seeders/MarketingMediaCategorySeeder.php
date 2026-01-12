<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MarketingMediaCategory;
use Illuminate\Database\Seeder;

class MarketingMediaCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Banner & Signage',
                'description' => 'Banners, signs, and promotional displays',
            ],
            [
                'name' => 'Print Materials',
                'description' => 'Brochures, leaflets, business cards, and other printed materials',
            ],
            [
                'name' => 'Promotional Items',
                'description' => 'Branded merchandise and giveaways',
            ],
            [
                'name' => 'Digital Media',
                'description' => 'Digital marketing materials and assets',
            ],
            [
                'name' => 'Display Materials',
                'description' => 'Point of sale displays and exhibition materials',
            ],
            [
                'name' => 'Marketing Kits',
                'description' => 'Complete marketing packages and kits',
            ],
        ];

        foreach ($categories as $category) {
            MarketingMediaCategory::updateOrCreate(
                ['name' => $category['name']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ]
            );
        }
    }
}
