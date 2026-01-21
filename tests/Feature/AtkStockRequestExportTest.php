<?php

namespace Tests\Feature;

use App\Filament\Resources\AtkStockRequests\Pages\ListAtkStockRequests;
use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkStockRequest;
use App\Models\User;
use App\Models\UserDivision;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\UserDivisionSeeder::class);

    Filament::setCurrentPanel(Filament::getPanel('dashboard'));

    $this->division = UserDivision::where('initial', 'IT')->first() ?? UserDivision::create(['name' => 'IT', 'initial' => 'IT']);

    static $counter = 0;
    $this->user = User::create([
        'name' => 'Super Admin',
        'email' => 'admin'.$counter.'@example.com',
        'password' => bcrypt('password'),
        'initial' => 'SA'.$counter++,
        'division_id' => $this->division->id,
    ]);
    $this->user->assignRole('Super Admin');

    $this->actingAs($this->user);
});

it('can export a single ATK stock request from table action', function () {
    Excel::fake();

    $request = AtkStockRequest::create([
        'request_number' => 'REQ-001',
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
        'request_type' => 'regular',
    ]);

    $category = AtkCategory::create(['name' => 'Stationery']);
    $item = AtkItem::create([
        'name' => 'Pen',
        'slug' => 'pen',
        'category_id' => $category->id,
        'unit_of_measure' => 'pcs',
    ]);

    $request->atkStockRequestItems()->create([
        'item_id' => $item->id,
        'category_id' => $category->id,
        'quantity' => 10,
    ]);

    Livewire::test(ListAtkStockRequests::class)
        ->assertCanSeeTableRecords([$request])
        ->callTableAction('export', $request);

    Excel::assertDownloaded('atk_stock_request_REQ-001.xlsx');
});

it('can bulk export ATK stock requests', function () {
    Excel::fake();
    Carbon::setTestNow('2026-01-21 10:00:00');

    $request1 = AtkStockRequest::create([
        'request_number' => 'REQ-001',
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
        'request_type' => 'regular',
    ]);

    $request2 = AtkStockRequest::create([
        'request_number' => 'REQ-002',
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
        'request_type' => 'regular',
    ]);

    $category = AtkCategory::create(['name' => 'Stationery']);
    $item = AtkItem::create([
        'name' => 'Pen',
        'slug' => 'pen',
        'category_id' => $category->id,
        'unit_of_measure' => 'pcs',
    ]);

    $request1->atkStockRequestItems()->create([
        'item_id' => $item->id,
        'category_id' => $category->id,
        'quantity' => 10,
    ]);

    $request2->atkStockRequestItems()->create([
        'item_id' => $item->id,
        'category_id' => $category->id,
        'quantity' => 20,
    ]);

    Livewire::test(ListAtkStockRequests::class)
        ->assertCanSeeTableRecords([$request1, $request2])
        ->callTableBulkAction('export', [$request1->id, $request2->id]);

    Excel::assertDownloaded('atk_stock_requests_2026-01-21_10-00-00.xlsx');

    Carbon::setTestNow();
});

it('can export a single ATK stock request from view page', function () {
    Excel::fake();

    $request = AtkStockRequest::create([
        'request_number' => 'REQ-003',
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
        'request_type' => 'regular',
    ]);

    $category = AtkCategory::create(['name' => 'Stationery']);
    $item = AtkItem::create([
        'name' => 'Pen',
        'slug' => 'pen-3',
        'category_id' => $category->id,
        'unit_of_measure' => 'pcs',
    ]);

    $request->atkStockRequestItems()->create([
        'item_id' => $item->id,
        'category_id' => $category->id,
        'quantity' => 10,
    ]);

    Livewire::test(\App\Filament\Resources\AtkStockRequests\Pages\ViewAtkStockRequest::class, [
        'record' => $request->id,
    ])
        ->callAction('export');

    Excel::assertDownloaded('atk_stock_request_REQ-003.xlsx');
});
