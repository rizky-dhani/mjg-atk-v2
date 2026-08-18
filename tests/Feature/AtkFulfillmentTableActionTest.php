<?php

namespace Tests\Feature;

use App\Enums\AtkStockRequestItemStatus;
use App\Enums\FulfillmentStatus;
use App\Filament\Resources\AtkFulfillments\Pages\ListAtkFulfillments;
use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkStockRequest;
use App\Models\FulfillmentHistory;
use App\Models\User;
use App\Models\UserDivision;
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

    $this->division = UserDivision::where('initial', 'ITD')->first();
    $this->user = User::factory()->create();
    $this->user->assignRole('Admin');
    $this->user->givePermissionTo(['view-any atk-fulfillment', 'view atk-fulfillment', 'edit atk-fulfillment']);
    $this->user->divisions()->attach($this->division);

    $this->requester = User::factory()->create();

    $this->category = AtkCategory::create(['name' => 'Stationery', 'slug' => 'atk']);
    $this->item = AtkItem::create([
        'name' => 'Test Item',
        'slug' => 'test-item',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);

    $this->request = AtkStockRequest::create([
        'request_number' => 'REQ-FUL-001',
        'requester_id' => $this->requester->id,
        'division_id' => $this->division->id,
        'status' => \App\Enums\AtkStockRequestStatus::Published,
    ]);
    $this->request->approval->update(['status' => 'approved']);

    $this->requestItem = $this->request->atkStockRequestItems()->create([
        'item_id' => $this->item->id,
        'category_id' => $this->category->id,
        'quantity' => 10,
    ]);

    $this->actingAs($this->user);
});

it('fulfills items from the table action with notes', function () {
    Livewire::test(ListAtkFulfillments::class)
        ->assertCanSeeTableRecords([$this->request])
        ->callTableAction('fulfill', $this->request, data: [
            'items' => [
                [
                    'item_id' => $this->requestItem->id,
                    'qty' => 4,
                ],
            ],
            'notes' => 'Catatan dari tabel',
        ])
        ->assertHasNoTableActionErrors()
        ->assertNotified('1 Item Berhasil Disimpan');

    $this->requestItem->refresh();
    expect($this->requestItem->received_quantity)->toBe(4);
    expect($this->requestItem->status)->toBe(AtkStockRequestItemStatus::PartiallyReceived);
    expect($this->request->fulfillment_status)->toBe(FulfillmentStatus::PartiallyFulfilled);

    $history = FulfillmentHistory::where('request_id', $this->request->id)->first();
    expect($history)->not->toBeNull();
    expect($history->quantity)->toBe(4);
    expect($history->notes)->toBe('Catatan dari tabel');
});

it('hides fulfill action for fully fulfilled requests', function () {
    $this->requestItem->update([
        'received_quantity' => 10,
        'status' => AtkStockRequestItemStatus::FullyReceived,
    ]);

    Livewire::test(ListAtkFulfillments::class)
        ->assertCanSeeTableRecords([$this->request])
        ->assertTableActionHidden('fulfill', $this->request);
});
