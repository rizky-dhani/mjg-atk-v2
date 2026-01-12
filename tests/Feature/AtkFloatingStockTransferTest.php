<?php

use App\Models\AtkItem;
use App\Models\AtkCategory;
use App\Models\UserDivision;
use App\Models\AtkFloatingStock;
use App\Models\AtkDivisionStock;
use App\Models\AtkFloatingStockTransactionHistory;
use App\Models\AtkStockTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = AtkCategory::create(['name' => 'Stationery', 'slug' => 'atk']);
    $this->item = AtkItem::create([
        'name' => 'Test Item',
        'slug' => 'test-item',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);
    $this->item2 = AtkItem::create([
        'name' => 'Test Item 2',
        'slug' => 'test-item-2',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);
    $this->division = UserDivision::create(['name' => 'IT', 'initial' => 'IT']);
    
    // Setup initial floating stock
    $this->floatingStock = AtkFloatingStock::create([
        'item_id' => $this->item->id,
        'category_id' => $this->category->id,
        'current_stock' => 100,
        'moving_average_cost' => 1000,
    ]);

    $this->floatingStock2 = AtkFloatingStock::create([
        'item_id' => $this->item2->id,
        'category_id' => $this->category->id,
        'current_stock' => 50,
        'moving_average_cost' => 2000,
    ]);
});

it('successfully distributes stock from floating to division', function () {
    $quantity = 10;
    $notes = 'Test distribution notes';
    
    $this->floatingStock->distributeToDivision($this->division->id, $quantity, $notes);

    // Verify Floating Stock decreased
    $this->floatingStock->refresh();
    expect($this->floatingStock->current_stock)->toBe(90);

    // Verify Division Stock increased
    $divisionStock = AtkDivisionStock::where('division_id', $this->division->id)
        ->where('item_id', $this->item->id)
        ->first();
    
    expect($divisionStock)->not->toBeNull();
    expect($divisionStock->current_stock)->toBe(10);
    expect($divisionStock->moving_average_cost)->toBe(1000);

    // Verify Floating Stock Transaction History
    $floatingTrx = AtkFloatingStockTransactionHistory::where('item_id', $this->item->id)
        ->where('type', 'out')
        ->latest()
        ->first();
    
    expect($floatingTrx->quantity)->toBe(10);
    expect($floatingTrx->destination_division_id)->toBe($this->division->id);
    expect($floatingTrx->notes)->toBe($notes);

    // Verify Division Stock Transaction History
    $divisionTrx = AtkStockTransaction::where('division_id', $this->division->id)
        ->where('item_id', $this->item->id)
        ->where('type', 'transfer')
        ->latest()
        ->first();
    
    expect($divisionTrx->quantity)->toBe(10);
    expect($divisionTrx->notes)->toBe($notes);
});

it('fails to distribute if quantity is invalid', function () {
    expect(fn() => $this->floatingStock->distributeToDivision($this->division->id, 0))
        ->toThrow(\InvalidArgumentException::class, 'Invalid quantity to distribute.');
        
    expect(fn() => $this->floatingStock->distributeToDivision($this->division->id, -1))
        ->toThrow(\InvalidArgumentException::class, 'Invalid quantity to distribute.');
});

it('fails to distribute if quantity exceeds current stock', function () {
    expect(fn() => $this->floatingStock->distributeToDivision($this->division->id, 101))
        ->toThrow(\InvalidArgumentException::class, 'Invalid quantity to distribute.');
});

it('successfully distributes bulk items to division', function () {
    $items = [
        ['item_id' => $this->item->id, 'quantity' => 20],
        ['item_id' => $this->item2->id, 'quantity' => 15],
    ];
    $notes = 'Bulk distribution notes';

    AtkFloatingStock::distributeBulkToDivision($items, $this->division->id, $notes);

    // Verify Item 1
    $this->floatingStock->refresh();
    expect($this->floatingStock->current_stock)->toBe(80);
    
    $divStock1 = AtkDivisionStock::where('division_id', $this->division->id)
        ->where('item_id', $this->item->id)
        ->first();
    expect($divStock1->current_stock)->toBe(20);

    // Verify Item 2
    $this->floatingStock2->refresh();
    expect($this->floatingStock2->current_stock)->toBe(35);
    
    $divStock2 = AtkDivisionStock::where('division_id', $this->division->id)
        ->where('item_id', $this->item2->id)
        ->first();
    expect($divStock2->current_stock)->toBe(15);
    
    // Verify notes are recorded for both
    expect(AtkFloatingStockTransactionHistory::where('item_id', $this->item->id)->latest()->first()->notes)->toBe($notes);
    expect(AtkFloatingStockTransactionHistory::where('item_id', $this->item2->id)->latest()->first()->notes)->toBe($notes);
});
