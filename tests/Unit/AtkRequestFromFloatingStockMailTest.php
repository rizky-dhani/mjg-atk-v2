<?php

namespace Tests\Unit;

use App\Mail\AtkRequestFromFloatingStockMail;
use App\Models\AtkItem;
use App\Models\AtkRequestFromFloatingStock;
use App\Models\AtkRequestFromFloatingStockItem;
use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtkRequestFromFloatingStockMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_atk_request_from_floating_stock_mail_has_correct_content(): void
    {
        $division = UserDivision::create(['name' => 'Marketing', 'initial' => 'MKT']);
        $requester = User::factory()->create(['name' => 'John Doe']);

        $request = AtkRequestFromFloatingStock::create([
            'request_number' => 'ATK-FS-20260113-0001',
            'requester_id' => $requester->id,
            'division_id' => $division->id,
        ]);

        $category = \App\Models\AtkCategory::create(['name' => 'Stationery']);

        $item = AtkItem::create([
            'name' => 'Paper',
            'slug' => 'paper',
            'category_id' => $category->id,
            'unit_of_measure' => 'rim',
        ]);
        AtkRequestFromFloatingStockItem::create([
            'request_id' => $request->id,
            'item_id' => $item->id,
            'quantity' => 10,
        ]);

        $actor = User::factory()->create(['name' => 'Admin User']);

        $mailable = new AtkRequestFromFloatingStockMail(
            stockRequest: $request,
            actionStatus: 'submitted',
            actor: $actor,
            recipientName: 'Jane Doe',
            viewUrl: 'http://localhost/admin/atk-requests-from-floating-stock/1'
        );

        $mailable->assertHasSubject('New Floating Stock Request: ATK-FS-20260113-0001');
        $mailable->assertSeeInHtml('Jane Doe');
        $mailable->assertSeeInHtml('ATK-FS-20260113-0001');
        $mailable->assertSeeInHtml('Submitted');
        $mailable->assertSeeInHtml('Paper');
        $mailable->assertSeeInHtml('10');
    }
}
