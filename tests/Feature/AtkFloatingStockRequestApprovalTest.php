<?php

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkFloatingStock;
use App\Models\AtkDivisionStock;
use App\Models\AtkRequestFromFloatingStock;
use App\Models\AtkRequestFromFloatingStockItem;
use App\Models\User;
use App\Models\UserDivision;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\UserDivisionSeeder::class);
    $this->seed(\Database\Seeders\ApprovalFlowSeeder::class);

    $this->division = UserDivision::where('initial', 'ITD')->first();
    $this->gaDivision = UserDivision::where('initial', 'GA')->first();
    
    $this->user = User::factory()->create(['division_id' => $this->division->id]);
    $this->gaAdmin = User::factory()->create(['division_id' => $this->gaDivision->id]);
    $this->gaAdmin->assignRole('Admin');
    
    $this->category = AtkCategory::create(['name' => 'Stationery', 'slug' => 'atk']);
    $this->item = AtkItem::create([
        'name' => 'Test Item',
        'slug' => 'test-item',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);

    // Setup initial floating stock
    $this->floatingStock = AtkFloatingStock::create([
        'item_id' => $this->item->id,
        'category_id' => $this->category->id,
        'current_stock' => 100,
        'moving_average_cost' => 1000,
    ]);
});

it('transfers stock from floating to division on final approval', function () {
    $request = AtkRequestFromFloatingStock::create([
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
    ]);

    $request->items()->create([
        'item_id' => $this->item->id,
        'quantity' => 10,
    ]);

    $approval = $request->approval;
    $approvalService = app(ApprovalService::class);

    // Mock the approval steps. We'll manually set it to approved to trigger handleStockUpdates
    // In a real flow, we'd go through each step. 
    // For this test, we verify the integration between Approval and StockUpdateService.
    
    $approval->update(['status' => 'approved']);
    
    $approvalService->handleStockUpdates($request);

    // Verify stock movement
    $this->floatingStock->refresh();
    expect($this->floatingStock->current_stock)->toBe(90);

    $divisionStock = AtkDivisionStock::where('division_id', $this->division->id)
        ->where('item_id', $this->item->id)
        ->first();
    
    expect($divisionStock)->not->toBeNull();
    expect($divisionStock->current_stock)->toBe(10);
});
