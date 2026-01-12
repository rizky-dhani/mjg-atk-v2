<?php

namespace Tests\Feature;

use App\Filament\Resources\AtkFloatingStocks\Pages\ListAtkFloatingStocks;
use App\Models\AtkCategory;
use App\Models\AtkFloatingStock;
use App\Models\AtkItem;
use App\Models\User;
use App\Models\UserDivision;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\UserDivisionSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('dashboard'));

    $this->division = UserDivision::where('initial', 'GA')->first();
    $this->targetDivision = UserDivision::where('initial', 'IT')->first()
        ?? UserDivision::first();

    $this->user = User::factory()->create(['division_id' => $this->division->id]);

    $this->category = AtkCategory::create(['name' => 'Stationery', 'slug' => 'atk']);

    $this->item1 = AtkItem::create([
        'name' => 'Item 1',
        'slug' => 'item-1',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);

    $this->item2 = AtkItem::create([
        'name' => 'Item 2',
        'slug' => 'item-2',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);

    $this->stock1 = AtkFloatingStock::create([
        'item_id' => $this->item1->id,
        'category_id' => $this->category->id,
        'current_stock' => 100,
        'moving_average_cost' => 1000,
    ]);

    $this->stock2 = AtkFloatingStock::create([
        'item_id' => $this->item2->id,
        'category_id' => $this->category->id,
        'current_stock' => 50,
        'moving_average_cost' => 2000,
    ]);

    $this->actingAs($this->user);
});

it('can bulk transfer stock via bulk action', function () {
    Livewire::test(ListAtkFloatingStocks::class)
        ->callTableBulkAction('bulk_transfer_floating_stock', [$this->stock1->id, $this->stock2->id], data: [
            'division_id' => $this->targetDivision->id,
            'notes' => 'Bulk transfer test',
        ])
        ->assertHasNoTableBulkActionErrors()
        ->assertNotified('Transfer Berhasil');

    $this->stock1->refresh();
    $this->stock2->refresh();

    expect($this->stock1->current_stock)->toBe(0);
    expect($this->stock2->current_stock)->toBe(0);

    $this->assertDatabaseHas('atk_stock_trx', [
        'division_id' => $this->targetDivision->id,
        'item_id' => $this->item1->id,
        'quantity' => 100,
    ]);

    $this->assertDatabaseHas('atk_stock_trx', [
        'division_id' => $this->targetDivision->id,
        'item_id' => $this->item2->id,
        'quantity' => 50,
    ]);
});
