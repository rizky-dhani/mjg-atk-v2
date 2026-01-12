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
        ?? UserDivision::where('initial', 'FIN')->first()
        ?? UserDivision::first();

    \Illuminate\Support\Facades\Log::info('Test Target Division', ['id' => $this->targetDivision->id]);

    $this->user = User::factory()->create(['division_id' => $this->division->id]);
    $this->category = AtkCategory::create(['name' => 'Stationery', 'slug' => 'atk']);
    $this->item = AtkItem::create([
        'name' => 'Test Item',
        'slug' => 'test-item',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);

    $this->floatingStock = AtkFloatingStock::create([
        'item_id' => $this->item->id,
        'category_id' => $this->category->id,
        'current_stock' => 100,
        'moving_average_cost' => 1000,
    ]);

    $this->actingAs($this->user);
});

it('can transfer stock via table action', function () {
    Livewire::test(ListAtkFloatingStocks::class)
        ->callTableAction('transfer', $this->floatingStock->id, data: [
            'division_id' => $this->targetDivision->id,
            'quantity' => 10,
            'notes' => 'Transfer test',
        ])
        ->assertHasNoTableActionErrors()
        ->assertNotified('Transfer Berhasil');

    $this->floatingStock->refresh();
    expect($this->floatingStock->current_stock)->toBe(90);

    $this->assertDatabaseHas('atk_stock_trx', [
        'division_id' => $this->targetDivision->id,
        'item_id' => $this->item->id,
        'quantity' => 10,
        'notes' => 'Transfer test',
    ]);
});

it('validates transfer quantity', function () {
    Livewire::test(ListAtkFloatingStocks::class)
        ->callTableAction('transfer', $this->floatingStock, data: [
            'division_id' => $this->targetDivision->id,
            'quantity' => 101, // More than current stock (100)
            'notes' => 'Transfer test',
        ])
        ->assertHasTableActionErrors(['quantity' => ['max']]);
});
