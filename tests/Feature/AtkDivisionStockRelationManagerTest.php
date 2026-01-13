<?php

use App\Filament\Resources\AtkDivisionStocks\RelationManagers\IncomingFloatingStockRequestsRelationManager;
use App\Models\AtkCategory;
use App\Models\AtkDivisionStock;
use App\Models\AtkItem;
use App\Models\AtkRequestFromFloatingStock;
use App\Models\AtkRequestFromFloatingStockItem;
use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('relation manager filters incoming floating stock requests correctly', function () {
    $division = UserDivision::create(['name' => 'Marketing', 'initial' => 'MKT']);
    $otherDivision = UserDivision::create(['name' => 'IT', 'initial' => 'IT']);

    $category = AtkCategory::create(['name' => 'Paper']);
    $item = AtkItem::create(['name' => 'A4 Paper', 'slug' => 'a4-paper', 'category_id' => $category->id, 'unit_of_measure' => 'rim']);
    $otherItem = AtkItem::create(['name' => 'Pen', 'slug' => 'pen', 'category_id' => $category->id, 'unit_of_measure' => 'pcs']);

    $divisionStock = AtkDivisionStock::create([
        'division_id' => $division->id,
        'item_id' => $item->id,
        'category_id' => $category->id,
        'current_stock' => 10,
    ]);

    $user = User::factory()->create();

    // 1. Valid request: Same division, same item
    $request1 = AtkRequestFromFloatingStock::create([
        'request_number' => 'REQ-MATCH',
        'requester_id' => $user->id,
        'division_id' => $division->id,
    ]);
    AtkRequestFromFloatingStockItem::create([
        'request_id' => $request1->id,
        'item_id' => $item->id,
        'quantity' => 5,
    ]);

    // 2. Invalid request: Different division
    $request2 = AtkRequestFromFloatingStock::create([
        'request_number' => 'REQ-DIFF-DIV',
        'requester_id' => $user->id,
        'division_id' => $otherDivision->id,
    ]);
    AtkRequestFromFloatingStockItem::create([
        'request_id' => $request2->id,
        'item_id' => $item->id,
        'quantity' => 5,
    ]);

    // 3. Invalid request: Same division, different item
    $request3 = AtkRequestFromFloatingStock::create([
        'request_number' => 'REQ-DIFF-ITEM',
        'requester_id' => $user->id,
        'division_id' => $division->id,
    ]);
    AtkRequestFromFloatingStockItem::create([
        'request_id' => $request3->id,
        'item_id' => $otherItem->id,
        'quantity' => 5,
    ]);

    Livewire::test(IncomingFloatingStockRequestsRelationManager::class, [
        'ownerRecord' => $divisionStock,
        'pageClass' => \App\Filament\Resources\AtkDivisionStocks\Pages\ViewAtkDivisionStock::class,
    ])
        ->assertCanSeeTableRecords([$request1->atkRequestFromFloatingStockItems->first()])
        ->assertCanNotSeeTableRecords([$request2->atkRequestFromFloatingStockItems->first()])
        ->assertCanNotSeeTableRecords([$request3->atkRequestFromFloatingStockItems->first()]);
});
