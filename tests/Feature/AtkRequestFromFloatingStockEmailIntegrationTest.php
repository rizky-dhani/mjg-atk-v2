<?php

use App\Mail\AtkRequestFromFloatingStockMail;
use App\Models\ApprovalFlow;
use App\Models\AtkRequestFromFloatingStock;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AtkRequestFromFloatingStockEmailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_creating_floating_stock_request_triggers_email(): void
    {
        $division = UserDivision::create(['name' => 'Marketing', 'initial' => 'MKT']);
        $role = Role::create(['name' => 'Manager', 'guard_name' => 'web']);
        
        $requester = User::factory()->create(['division_id' => $division->id]);
        $approver = User::factory()->create(['division_id' => $division->id]);
        $approver->assignRole($role);
        
        // Setup Approval Flow
        $flow = ApprovalFlow::create([
            'name' => 'Floating Stock Flow',
            'model_type' => AtkRequestFromFloatingStock::class,
            'is_active' => true,
        ]);

        $step = $flow->approvalFlowSteps()->create([
            'step_name' => 'Manager Approval',
            'step_number' => 1,
            'division_id' => $division->id,
            'role_id' => $role->id,
        ]);

        $this->actingAs($requester);

        $request = AtkRequestFromFloatingStock::create([
            'requester_id' => $requester->id,
            'division_id' => $division->id,
            'request_number' => 'ATK-FS-20260113-0001',
        ]);

        app(\App\Services\ApprovalProcessingService::class)->createApproval($request, AtkRequestFromFloatingStock::class);

        // We expect an email to be sent to the requester
        Mail::assertSent(AtkRequestFromFloatingStockMail::class, function ($mail) use ($requester) {
            return $mail->hasTo($requester->email) && $mail->actionStatus === 'submitted';
        });

        // We expect an email to be sent to the approver
        Mail::assertSent(AtkRequestFromFloatingStockMail::class, function ($mail) use ($approver) {
            return $mail->hasTo($approver->email) && $mail->actionStatus === 'submitted' && $mail->isApprover === true;
        });
    }
}