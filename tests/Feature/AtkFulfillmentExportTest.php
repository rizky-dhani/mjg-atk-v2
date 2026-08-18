<?php

namespace Tests\Feature;

use App\Enums\AtkStockRequestItemStatus;
use App\Exports\AtkFulfillmentExport;
use App\Filament\Resources\AtkFulfillments\Pages\ListAtkFulfillments;
use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkStockRequest;
use App\Models\User;
use App\Models\UserDivision;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

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

it('exports approved fulfillments with item rows', function () {
    Excel::fake();

    $this->requestItem->update(['received_quantity' => 6, 'status' => AtkStockRequestItemStatus::PartiallyReceived]);

    Livewire::test(ListAtkFulfillments::class)
        ->assertCanSeeTableRecords([$this->request])
        ->callAction('export');

    Excel::assertDownloaded('atk_fulfillments_'.now()->format('Y-m-d_H-i-s').'.xlsx');
});

it('export query only includes approved published requests', function () {
    $pendingRequest = AtkStockRequest::create([
        'request_number' => 'REQ-FUL-002',
        'requester_id' => $this->requester->id,
        'division_id' => $this->division->id,
        'status' => \App\Enums\AtkStockRequestStatus::Published,
    ]);
    $pendingRequest->atkStockRequestItems()->create([
        'item_id' => $this->item->id,
        'category_id' => $this->category->id,
        'quantity' => 5,
    ]);

    $export = new AtkFulfillmentExport([$this->request->id, $pendingRequest->id]);

    expect($export->query()->pluck('request_id')->unique()->toArray())->toBe([$this->request->id]);
});
