<?php

use App\Mail\AtkStockRequestMail;
use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowStep;
use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkStockRequest;
use App\Models\User;
use App\Models\UserDivision;
use App\Services\ApprovalProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    $this->division = UserDivision::create([
        'name' => 'IT',
        'initial' => 'IT',
    ]);

    $this->adminRole = Role::create(['name' => 'Admin']);
    $this->staffRole = Role::create(['name' => 'Staff']);

    $this->requester = User::factory()->create([
        'division_id' => $this->division->id,
        'email' => 'requester@example.com',
        'initial' => 'REQ',
    ]);
    $this->requester->assignRole($this->staffRole);

    $this->approver = User::factory()->create([
        'division_id' => $this->division->id,
        'email' => 'approver@example.com',
        'initial' => 'APP',
    ]);
    $this->approver->assignRole($this->adminRole);

    // Setup Approval Flow
    $this->flow = ApprovalFlow::create([
        'name' => 'ATK Stock Request Flow',
        'model_type' => AtkStockRequest::class,
        'is_active' => true,
    ]);

    $this->step = ApprovalFlowStep::create([
        'flow_id' => $this->flow->id,
        'step_name' => 'Admin Approval',
        'step_number' => 1,
        'role_id' => $this->adminRole->id,
        'division_id' => $this->division->id,
    ]);

    $this->category = AtkCategory::create(['name' => 'Stationery']);
    $this->item = AtkItem::create([
        'name' => 'Pen',
        'slug' => 'pen',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);
});

it('sends email when stock request is created', function () {
    $this->actingAs($this->requester);

    $stockRequest = AtkStockRequest::create([
        'requester_id' => $this->requester->id,
        'division_id' => $this->division->id,
        'request_type' => 'office_stationery',
    ]);

    $stockRequest->atkStockRequestItems()->create([
        'item_id' => $this->item->id,
        'category_id' => $this->category->id,
        'quantity' => 10,
    ]);

    $service = app(ApprovalProcessingService::class);
    $service->createApproval($stockRequest, AtkStockRequest::class);

    Mail::assertSent(AtkStockRequestMail::class, function ($mail) {
        return $mail->hasTo('requester@example.com') &&
               $mail->hasTo('approver@example.com') &&
               $mail->actionStatus === 'submitted';
    });
});

it('sends email when stock request is approved', function () {
    $this->actingAs($this->requester);

    $stockRequest = AtkStockRequest::create([
        'requester_id' => $this->requester->id,
        'division_id' => $this->division->id,
        'request_type' => 'office_stationery',
    ]);

    $stockRequest->atkStockRequestItems()->create([
        'item_id' => $this->item->id,
        'category_id' => $this->category->id,
        'quantity' => 10,
    ]);

    $service = app(ApprovalProcessingService::class);
    $approval = $service->createApproval($stockRequest, AtkStockRequest::class);

    // Approve step 1
    $service->processApprovalStep($approval, $this->approver, 'approve', 'Approved by test');

    Mail::assertSent(AtkStockRequestMail::class, function ($mail) {
        return $mail->hasTo('requester@example.com') &&
               $mail->actionStatus === 'approved';
    });
});

it('sends email when stock request is rejected', function () {
    $this->actingAs($this->requester);

    $stockRequest = AtkStockRequest::create([
        'requester_id' => $this->requester->id,
        'division_id' => $this->division->id,
        'request_type' => 'office_stationery',
    ]);

    $stockRequest->atkStockRequestItems()->create([
        'item_id' => $this->item->id,
        'category_id' => $this->category->id,
        'quantity' => 10,
    ]);

    $service = app(ApprovalProcessingService::class);
    $approval = $service->createApproval($stockRequest, AtkStockRequest::class);

    // Reject
    $service->processApprovalStep($approval, $this->approver, 'reject', 'Rejected by test');

    Mail::assertSent(AtkStockRequestMail::class, function ($mail) {
        return $mail->hasTo('requester@example.com') &&
               $mail->actionStatus === 'rejected';
    });
});

it('sends email to next approver when partially approved', function () {
    // Add a second step to the flow
    $this->managerRole = Role::create(['name' => 'Manager']);
    $this->manager = User::factory()->create([
        'division_id' => $this->division->id,
        'email' => 'manager@example.com',
        'initial' => 'MGR',
    ]);
    $this->manager->assignRole($this->managerRole);

    $this->step2 = ApprovalFlowStep::create([
        'flow_id' => $this->flow->id,
        'step_name' => 'Manager Approval',
        'step_number' => 2,
        'role_id' => $this->managerRole->id,
        'division_id' => $this->division->id,
    ]);

    $this->actingAs($this->requester);

    $stockRequest = AtkStockRequest::create([
        'requester_id' => $this->requester->id,
        'division_id' => $this->division->id,
        'request_type' => 'office_stationery',
    ]);

    $stockRequest->atkStockRequestItems()->create([
        'item_id' => $this->item->id,
        'category_id' => $this->category->id,
        'quantity' => 10,
    ]);

    $service = app(ApprovalProcessingService::class);
    $approval = $service->createApproval($stockRequest, AtkStockRequest::class);

    // Approve step 1
    $service->processApprovalStep($approval, $this->approver, 'approve', 'Approved step 1');

    Mail::assertSent(AtkStockRequestMail::class, function ($mail) {
        return $mail->hasTo('requester@example.com') &&
               $mail->hasTo('manager@example.com') &&
               $mail->actionStatus === 'partially_approved';
    });
});
