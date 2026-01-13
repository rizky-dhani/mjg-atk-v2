<?php

namespace Tests\Unit;

use App\Models\AtkCategory;
use App\Models\AtkDivisionStock;
use App\Models\AtkItem;
use App\Models\AtkRequestFromFloatingStock;
use App\Models\AtkRequestFromFloatingStockItem;
use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtkDivisionStockIncomingFloatingStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_incoming_floating_stock_requests_relationship(): void
    {
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
            'request_number' => 'REQ-001',
            'requester_id' => $user->id,
            'division_id' => $division->id,
        ]);
        $reqItem1 = AtkRequestFromFloatingStockItem::create([
            'request_id' => $request1->id,
            'item_id' => $item->id,
            'quantity' => 5,
        ]);

        // 2. Invalid request: Different division
        $request2 = AtkRequestFromFloatingStock::create([
            'request_number' => 'REQ-002',
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
            'request_number' => 'REQ-003',
            'requester_id' => $user->id,
            'division_id' => $division->id,
        ]);
        AtkRequestFromFloatingStockItem::create([
            'request_id' => $request3->id,
            'item_id' => $otherItem->id,
            'quantity' => 5,
        ]);

        $results = $divisionStock->incomingFloatingStockRequests()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($reqItem1->id, $results->first()->id);
        $this->assertEquals('REQ-001', $results->first()->request->request_number);
    }
}