<?php

namespace Tests\Feature;

use App\Models\AtkCategory;
use App\Models\AtkDivisionStock;
use App\Models\AtkItem;
use App\Models\AtkStockRequest;
use App\Models\User;
use App\Models\UserDivision;
use App\Enums\AtkStockRequestItemStatus;
use App\Enums\FulfillmentStatus;
use App\Filament\Resources\AtkStockRequests\RelationManagers\AtkStockRequestItemsRelationManager;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\UserDivisionSeeder::class);
    $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);
    
    Filament::setCurrentPanel(Filament::getPanel('dashboard'));

    $this->division = UserDivision::where('initial', 'ITD')->first();
    $this->user = User::factory()->create(['division_id' => $this->division->id]);
    $this->user->assignRole('Admin');

    $this->category = AtkCategory::create(['name' => 'Stationery', 'slug' => 'atk']);
    $this->item = AtkItem::create([
        'name' => 'Test Item',
        'slug' => 'test-item',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);

    $this->request = AtkStockRequest::create([
        'request_number' => 'REQ-001',
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
        'status' => \App\Enums\AtkStockRequestStatus::Published,
    ]);

    $this->requestItem = $this->request->atkStockRequestItems()->create([
        'item_id' => $this->item->id,
        'category_id' => $this->category->id,
        'quantity' => 10,
    ]);

    // Mock approved status for fulfillment buttons to show
    $this->request->approval->update([
        'status' => 'approved',
    ]);

    $this->actingAs($this->user);
});

it('can partially fulfill a stock request item', function () {
    Livewire::test(AtkStockRequestItemsRelationManager::class, [
        'ownerRecord' => $this->request,
        'pageClass' => \App\Filament\Resources\AtkStockRequests\Pages\ViewAtkStockRequest::class,
    ])
        ->assertCanSeeTableRecords([$this->requestItem])
        ->mountTableAction('store_stock', $this->requestItem->id)
        ->set('mountedActions.0.data.qty', 4)
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $this->requestItem->refresh();
    expect($this->requestItem->received_quantity)->toBe(4);
    expect($this->requestItem->status)->toBe(AtkStockRequestItemStatus::PartiallyReceived);
    expect($this->request->fulfillment_status)->toBe(FulfillmentStatus::PartiallyFulfilled);

    // Verify stock increase
    $stock = AtkDivisionStock::where('division_id', $this->division->id)
        ->where('item_id', $this->item->id)
        ->first();
    
    expect($stock->current_stock)->toBe(4);
});

it('can fully fulfill a stock request item', function () {
    Livewire::test(AtkStockRequestItemsRelationManager::class, [
        'ownerRecord' => $this->request,
        'pageClass' => \App\Filament\Resources\AtkStockRequests\Pages\ViewAtkStockRequest::class,
    ])
        ->assertCanSeeTableRecords([$this->requestItem])
        ->mountTableAction('store_stock', $this->requestItem->id)
        ->set('mountedActions.0.data.qty', 10)
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $this->requestItem->refresh();
    expect($this->requestItem->received_quantity)->toBe(10);
    expect($this->requestItem->status)->toBe(AtkStockRequestItemStatus::FullyReceived);
    expect($this->request->fulfillment_status)->toBe(FulfillmentStatus::Fulfilled);
});

it('can bulk fulfill stock request items', function () {
    $item2 = AtkItem::create([
        'name' => 'Test Item 2',
        'slug' => 'test-item-2',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);

    $requestItem2 = $this->request->atkStockRequestItems()->create([
        'item_id' => $item2->id,
        'category_id' => $this->category->id,
        'quantity' => 5,
    ]);

    Livewire::test(AtkStockRequestItemsRelationManager::class, [
        'ownerRecord' => $this->request,
        'pageClass' => \App\Filament\Resources\AtkStockRequests\Pages\ViewAtkStockRequest::class,
    ])
        ->callTableBulkAction('bulk_store_stock', [$this->requestItem->id, $requestItem2->id])
        ->assertHasNoTableBulkActionErrors();

    $this->requestItem->refresh();
    $requestItem2->refresh();

    expect($this->requestItem->status)->toBe(AtkStockRequestItemStatus::FullyReceived);
    expect($requestItem2->status)->toBe(AtkStockRequestItemStatus::FullyReceived);
    expect($this->request->fulfillment_status)->toBe(FulfillmentStatus::Fulfilled);
});