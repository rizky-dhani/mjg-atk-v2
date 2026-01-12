<?php

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkRequestFromFloatingStock;
use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->division = UserDivision::create(['name' => 'IT', 'initial' => 'IT']);
    $this->user = User::factory()->create(['division_id' => $this->division->id]);
    $this->category = AtkCategory::create(['name' => 'Stationery', 'slug' => 'atk']);
    $this->item = AtkItem::create([
        'name' => 'Test Item',
        'slug' => 'test-item',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);
});

it('can create a request from floating stock', function () {
    $request = AtkRequestFromFloatingStock::create([
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
        'notes' => 'Test notes',
    ]);

    expect($request->request_number)->not->toBeEmpty();
    expect($request->request_number)->toStartWith('ATK-FLOAT-IT-REQ-');
    expect($request->requester_id)->toBe($this->user->id);
    expect($request->division_id)->toBe($this->division->id);
    expect($request->notes)->toBe('Test notes');
});

it('can add items to a request', function () {
    $request = AtkRequestFromFloatingStock::create([
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
    ]);

    $item = $request->items()->create([
        'item_id' => $this->item->id,
        'quantity' => 5,
    ]);

    expect($request->items)->toHaveCount(1);
    expect($request->items->first()->item_id)->toBe($this->item->id);
    expect($request->items->first()->quantity)->toBe(5);
});

it('automatically creates an approval record on creation', function () {
    // We need an approval flow for this model type
    $flow = \App\Models\ApprovalFlow::create([
        'name' => 'Floating Stock Request Flow',
        'model_type' => AtkRequestFromFloatingStock::class,
        'is_active' => true,
    ]);

    $request = AtkRequestFromFloatingStock::create([
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
    ]);

    expect($request->approval)->not->toBeNull();
    expect($request->approval->flow_id)->toBe($flow->id);
    expect($request->approval->status)->toBe('pending');
});
