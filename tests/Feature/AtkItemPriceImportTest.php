<?php

namespace Tests\Feature;

use App\Imports\AtkItemPriceImport;
use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkItemPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\UserDivisionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);

    $this->cat1 = AtkCategory::create(['name' => 'Kertas']);
    $this->cat2 = AtkCategory::create(['name' => 'Pulpen']);
});

it('can import item prices from a collection', function () {
    $import = new AtkItemPriceImport('2026-02-01');

    $rows = new Collection([
        [
            'no' => 'A',
            'item_description' => 'Kertas',
            'uom' => null,
            'harga' => null,
        ],
        [
            'no' => '1',
            'item_description' => 'Kertas A4 80Gr',
            'uom' => 'rim',
            'harga' => 47000,
        ],
        [
            'no' => 'B',
            'item_description' => 'Pulpen',
            'uom' => null,
            'harga' => null,
        ],
        [
            'no' => '1',
            'item_description' => 'Pulpen Faster',
            'uom' => 'pcs',
            'harga' => 3500,
        ],
    ]);

    $import->collection($rows);

    expect($import->processedCount)->toBe(2);

    // Check Kertas A4 80Gr
    $item1 = AtkItem::where('name', 'Kertas A4 80Gr')->first();
    expect($item1)->not->toBeNull();
    expect($item1->category_id)->toBe($this->cat1->id);
    expect($item1->unit_of_measure)->toBe('rim');

    $price1 = AtkItemPrice::where('item_id', $item1->id)->first();
    expect($price1->unit_price)->toBe(47000);
    expect($price1->effective_date->format('Y-m-d'))->toBe('2026-02-01');
    expect($price1->is_active)->toBeTrue();

    // Check Pulpen Faster
    $item2 = AtkItem::where('name', 'Pulpen Faster')->first();
    expect($item2)->not->toBeNull();
    expect($item2->category_id)->toBe($this->cat2->id);
    expect($item2->unit_of_measure)->toBe('pcs');

    $price2 = AtkItemPrice::where('item_id', $item2->id)->first();
    expect($price2->unit_price)->toBe(3500);
    expect($price2->is_active)->toBeTrue();
});

it('updates existing item details during import', function () {
    $item = AtkItem::create([
        'name' => 'Existing Item',
        'slug' => 'existing-item',
        'unit_of_measure' => 'box',
        'category_id' => $this->cat1->id,
    ]);

    $import = new AtkItemPriceImport('2026-02-01');

    $rows = new Collection([
        [
            'no' => '1',
            'item_description' => 'Existing Item',
            'uom' => 'pcs', // Changed from box
            'harga' => 1000,
        ],
    ]);

    $import->collection($rows);

    $item->refresh();
    expect($item->unit_of_measure)->toBe('pcs');

    $price = AtkItemPrice::where('item_id', $item->id)->first();
    expect($price->unit_price)->toBe(1000);
});

it('deactivates old prices when importing new ones', function () {
    $item = AtkItem::create([
        'name' => 'Price Item',
        'slug' => 'price-item',
        'category_id' => $this->cat1->id,
        'unit_of_measure' => 'pcs',
    ]);

    AtkItemPrice::create([
        'item_id' => $item->id,
        'category_id' => $this->cat1->id,
        'unit_price' => 500,
        'effective_date' => '2026-01-01',
        'is_active' => true,
    ]);

    $import = new AtkItemPriceImport('2026-02-01');

    $rows = new Collection([
        [
            'no' => '1',
            'item_description' => 'Price Item',
            'uom' => 'pcs',
            'harga' => 600,
        ],
    ]);

    $import->collection($rows);

    $oldPrice = AtkItemPrice::where('item_id', $item->id)->where('unit_price', 500)->first();
    $newPrice = AtkItemPrice::where('item_id', $item->id)->where('unit_price', 600)->first();

    expect($oldPrice->is_active)->toBeFalse();
    expect($newPrice->is_active)->toBeTrue();
});
