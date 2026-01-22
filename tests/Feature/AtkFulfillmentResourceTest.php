<?php

namespace Tests\Feature;

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkStockRequest;
use App\Models\User;
use App\Models\UserDivision;
use App\Models\AtkFulfillment;
use App\Filament\Resources\AtkFulfillments\AtkFulfillmentResource;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\UserDivisionSeeder::class);
    $this->seed(\Database\Seeders\PermissionSeeder::class);
    $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);
    
    Filament::setCurrentPanel(Filament::getPanel('dashboard'));

    $this->ipcDivision = UserDivision::where('initial', 'IPC')->first();
    $this->otherDivision = UserDivision::where('initial', 'ITD')->first();

    $this->ipcUser = User::factory()->create(['division_id' => $this->ipcDivision->id]);
    $this->ipcUser->givePermissionTo(['view-any atk-fulfillment', 'view atk-fulfillment', 'edit atk-fulfillment']);

    $this->otherUser = User::factory()->create(['division_id' => $this->otherDivision->id]);
    $this->otherUser->givePermissionTo(['view-any atk-fulfillment', 'view atk-fulfillment', 'edit atk-fulfillment']);

    $this->category = AtkCategory::create(['name' => 'Stationery', 'slug' => 'atk']);
    $this->item = AtkItem::create([
        'name' => 'Test Item',
        'slug' => 'test-item',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);

    // Create an approved request
    $this->approvedRequest = AtkStockRequest::create([
        'request_number' => 'REQ-APP-001',
        'requester_id' => $this->otherUser->id,
        'division_id' => $this->otherDivision->id,
        'status' => \App\Enums\AtkStockRequestStatus::Published,
    ]);
    $this->approvedRequest->approval->update(['status' => 'approved']);

    // Create a pending request
    $this->pendingRequest = AtkStockRequest::create([
        'request_number' => 'REQ-PEN-001',
        'requester_id' => $this->otherUser->id,
        'division_id' => $this->otherDivision->id,
        'status' => \App\Enums\AtkStockRequestStatus::Published,
    ]);
});

it('allows IPC users to see the fulfillment list', function () {
    $this->actingAs($this->ipcUser);

    Livewire::test(\App\Filament\Resources\AtkFulfillments\Pages\ListAtkFulfillments::class)
        ->assertCanSeeTableRecords([$this->approvedRequest])
        ->assertCanNotSeeTableRecords([$this->pendingRequest]);
});

it('denies non-IPC users from seeing the fulfillment list via policy', function () {
    $this->actingAs($this->otherUser);

    $this->get(AtkFulfillmentResource::getUrl('index'))
        ->assertForbidden();
});

it('allows super admin to see the fulfillment list', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Admin');
    
    $this->actingAs($superAdmin);

    Livewire::test(\App\Filament\Resources\AtkFulfillments\Pages\ListAtkFulfillments::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$this->approvedRequest]);
});

it('can see the items in the relation manager', function () {
    $this->actingAs($this->ipcUser);

    // Create an item for the request
    $requestItem = $this->approvedRequest->atkStockRequestItems()->create([
        'item_id' => $this->item->id,
        'category_id' => $this->category->id,
        'quantity' => 10,
    ]);

    Livewire::test(\App\Filament\Resources\AtkStockRequests\RelationManagers\AtkStockRequestItemsRelationManager::class, [
        'ownerRecord' => $this->approvedRequest,
        'pageClass' => \App\Filament\Resources\AtkFulfillments\Pages\ViewAtkFulfillment::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords($this->approvedRequest->atkStockRequestItems)
        ->mountTableAction('store_stock', $requestItem->id)
        ->set('mountedActions.0.data.qty', 5)
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $requestItem->refresh();
    expect($requestItem->received_quantity)->toBe(5);
});
