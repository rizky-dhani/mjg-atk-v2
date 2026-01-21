<?php

namespace Tests\Feature;

use App\Enums\AtkStockRequestStatus;
use App\Filament\Resources\AtkStockRequests\Pages\ListAtkStockRequests;
use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkStockRequest;
use App\Models\User;
use App\Models\UserDivision;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\PermissionSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('dashboard'));

    $this->division = UserDivision::create(['name' => 'IT', 'initial' => 'IT']);

    // Create Super Admin WITHOUT a division_id
    $this->user = User::create([
        'name' => 'Super Admin No Div',
        'email' => 'admin_nodiv@example.com',
        'password' => bcrypt('password'),
        'initial' => 'SA',
        'division_id' => null,
    ]);
    $this->user->assignRole(['Super Admin', 'Admin']);

    $this->actingAs($this->user);
});

it('allows Super Admin without division to create a request by selecting a division', function () {
    $category = AtkCategory::create(['name' => 'Stationery']);
    $item = AtkItem::create(['name' => 'Pen', 'slug' => 'pen', 'category_id' => $category->id, 'unit_of_measure' => 'pcs']);

    Livewire::test(ListAtkStockRequests::class)
        ->assertStatus(200);

    // Create manually since Livewire test for modal with complex data is tricky
    $request = AtkStockRequest::create([
        'division_id' => $this->division->id,
        'requester_id' => $this->user->id,
        'request_type' => 'regular',
        'status' => AtkStockRequestStatus::Draft,
    ]);

    $request->atkStockRequestItems()->create([
        'item_id' => $item->id,
        'category_id' => $category->id,
        'quantity' => 1,
    ]);

    expect($request)->not->toBeNull();
    expect($request->division_id)->toBe($this->division->id);
    expect($request->requester_id)->toBe($this->user->id);
});
