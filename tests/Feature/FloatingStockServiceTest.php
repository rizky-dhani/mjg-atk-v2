<?php

use App\Models\AtkItem;
use App\Models\AtkCategory;
use App\Models\UserDivision;
use App\Models\AtkStockRequest;
use App\Services\FloatingStockService;
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
    $this->division = UserDivision::create(['name' => 'IT', 'initial' => 'IT']);
    $this->service = new FloatingStockService();
});

it('stores source_division_id for incoming transactions', function () {
    $trx = $this->service->recordTransaction(
        $this->item->id,
        'in',
        10,
        1000,
        null,
        $this->division->id
    );

    expect($trx->source_division_id)->toBe($this->division->id);
    expect($trx->destination_division_id)->toBeNull();
    expect($trx->type)->toBe('in');
});

it('stores destination_division_id for outgoing transactions', function () {
    $trx = $this->service->recordTransaction(
        $this->item->id,
        'out',
        5,
        1000,
        null,
        null,
        $this->division->id
    );

    expect($trx->source_division_id)->toBeNull();
    expect($trx->destination_division_id)->toBe($this->division->id);
    expect($trx->type)->toBe('out');
});

it('infers source_division_id from transactionSource for incoming', function () {
    $staff = \App\Models\User::factory()->create(['division_id' => $this->division->id]);
    
    $request = AtkStockRequest::create([
        'request_number' => 'REQ-001',
        'requester_id' => $staff->id,
        'division_id' => $this->division->id,
        'request_type' => 'addition',
    ]);

    $trx = $this->service->recordTransaction(
        $this->item->id,
        'in',
        10,
        1000,
        $request
    );

    expect($trx->source_division_id)->toBe($this->division->id);
    expect($trx->destination_division_id)->toBeNull();
});

it('infers destination_division_id from transactionSource for outgoing', function () {
    $staff = \App\Models\User::factory()->create(['division_id' => $this->division->id]);
    
    $usage = \App\Models\AtkStockUsage::create([
        'request_number' => 'USG-001',
        'requester_id' => $staff->id,
        'division_id' => $this->division->id,
        'request_type' => 'reduction',
    ]);

    $trx = $this->service->recordTransaction(
        $this->item->id,
        'out',
        5,
        1000,
        $usage
    );

    expect($trx->source_division_id)->toBeNull();
    expect($trx->destination_division_id)->toBe($this->division->id);
});

