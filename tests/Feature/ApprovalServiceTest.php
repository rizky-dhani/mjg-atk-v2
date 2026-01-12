<?php

use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowStep;
use App\Models\AtkStockRequest;
use App\Models\User;
use App\Models\UserDivision;
use App\Services\ApprovalService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('dashboard'));
    Mail::fake();
    Notification::fake();

    $this->division = UserDivision::create([
        'name' => 'IT Division',
        'initial' => 'IT',
    ]);

    $this->otherDivision = UserDivision::create([
        'name' => 'Finance Division',
        'initial' => 'FIN',
    ]);

    $this->headRole = Role::create(['name' => 'Head', 'guard_name' => 'web']);
    $this->financeRole = Role::create(['name' => 'Finance', 'guard_name' => 'web']);

    $this->staff = User::factory()->create([
        'division_id' => $this->division->id,
    ]);

    $this->head = User::factory()->create([
        'division_id' => $this->division->id,
    ]);
    $this->head->assignRole($this->headRole);

    $this->finance = User::factory()->create([
        'division_id' => $this->otherDivision->id,
    ]);
    $this->finance->assignRole($this->financeRole);

    $this->flow = ApprovalFlow::create([
        'name' => 'Stock Request Flow',
        'model_type' => AtkStockRequest::class,
        'is_active' => true,
    ]);

    $this->step1 = ApprovalFlowStep::create([
        'flow_id' => $this->flow->id,
        'step_name' => 'Division Head Approval',
        'step_number' => 1,
        'role_id' => $this->headRole->id,
        'division_id' => null, // Means same division as requester
    ]);

    $this->step2 = ApprovalFlowStep::create([
        'flow_id' => $this->flow->id,
        'step_name' => 'Finance Approval',
        'step_number' => 2,
        'role_id' => $this->financeRole->id,
        'division_id' => $this->otherDivision->id,
    ]);

    $this->stockRequest = AtkStockRequest::create([
        'request_number' => 'REQ-001',
        'requester_id' => $this->staff->id,
        'division_id' => $this->division->id,
        'request_type' => 'addition',
    ]);

    $this->actingAs($this->staff);
    $this->approvalService = app(ApprovalService::class);
});

it('correctly identifies the next approver in step 1', function () {
    // Note: Approval is already created by StockRequestModelTrait created hook
    $approval = $this->stockRequest->approval;

    expect($approval)->not->toBeNull();
    expect($approval->current_step)->toBe(1);

    // Head of same division should be able to approve
    expect($this->approvalService->canUserApprove($this->stockRequest, $this->head))->toBeTrue();

    // Finance should NOT be able to approve step 1 (wrong role and division)
    expect($this->approvalService->canUserApprove($this->stockRequest, $this->finance))->toBeFalse();

    // Staff should NOT be able to approve (wrong role)
    expect($this->approvalService->canUserApprove($this->stockRequest, $this->staff))->toBeFalse();
});

it('correctly identifies multiple potential approvers for a step', function () {
    $otherHead = User::factory()->create([
        'division_id' => $this->division->id,
    ]);
    $otherHead->assignRole($this->headRole);

    // Both $this->head and $otherHead should be able to approve step 1
    expect($this->approvalService->canUserApprove($this->stockRequest, $this->head))->toBeTrue();
    expect($this->approvalService->canUserApprove($this->stockRequest, $otherHead))->toBeTrue();
});

it('correctly transitions through multiple steps', function () {
    $approval = $this->stockRequest->approval;

    // Step 1: Head Approval
    $this->approvalService->processApprovalStep($approval, $this->head, 'approve');

    $approval->refresh();
    expect($approval->current_step)->toBe(2);
    expect($approval->status)->toBe('pending');

    // Now Finance (in otherDivision) should be able to approve
    expect($this->approvalService->canUserApprove($this->stockRequest, $this->finance))->toBeTrue();

    // Head should no longer be able to approve
    expect($this->approvalService->canUserApprove($this->stockRequest, $this->head))->toBeFalse();

    // Step 2: Finance Approval
    $this->approvalService->processApprovalStep($approval, $this->finance, 'approve');

    $approval->refresh();
    expect($approval->status)->toBe('approved');
});

it('stops the flow if a request is rejected', function () {
    $approval = $this->stockRequest->approval;

    // Step 1: Reject by Head
    $this->approvalService->processApprovalStep($approval, $this->head, 'reject', 'Not needed');

    $approval->refresh();
    expect($approval->status)->toBe('rejected');

    // Finance should NOT be able to approve a rejected request
    expect($this->approvalService->canUserApprove($this->stockRequest, $this->finance))->toBeFalse();
});

it('does not adjust stock until final approval', function () {
    // Setup item and initial stock
    $category = \App\Models\AtkCategory::create(['name' => 'Stationery', 'slug' => 'atk']);
    $item = \App\Models\AtkItem::create([
        'name' => 'Pen',
        'slug' => 'pen',
        'category_id' => $category->id,
        'unit_of_measure' => 'pcs',
    ]);
    \App\Models\AtkItemPrice::create([
        'item_id' => $item->id,
        'category_id' => $category->id,
        'unit_price' => 1000,
        'effective_date' => now(),
        'is_active' => true,
    ]);

    // Initial stock is 0
    $divisionStock = \App\Models\AtkDivisionStock::create([
        'division_id' => $this->division->id,
        'item_id' => $item->id,
        'category_id' => $category->id,
        'current_stock' => 0,
        'moving_average_cost' => 0,
    ]);

    // Add item to request
    \App\Models\AtkStockRequestItem::create([
        'request_id' => $this->stockRequest->id,
        'item_id' => $item->id,
        'category_id' => $category->id,
        'quantity' => 10,
    ]);

    $approval = $this->stockRequest->approval;

    // Step 1: Approve by Head
    $this->approvalService->processApprovalStep($approval, $this->head, 'approve');

    $approval->refresh();
    $this->stockRequest->refresh();
    $this->stockRequest->load('approval');

    $divisionStock->refresh();
    expect($divisionStock->current_stock)->toBe(0); // Stock should NOT be updated yet

    // Step 2: Approve by Finance (Final step)
    $this->approvalService->processApprovalStep($approval, $this->finance, 'approve');

    $divisionStock->refresh();
    expect($divisionStock->current_stock)->toBe(10); // Stock SHOULD be updated now
});

it('dispatches email to the next approver', function () {
    // Initial creation dispatches email to initial approver (Head)
    $approval = $this->approvalService->createApproval($this->stockRequest, AtkStockRequest::class);

    Mail::assertSent(\App\Mail\AtkStockRequestMail::class, function ($mail) {
        return $mail->hasTo($this->head->email) && $mail->actionStatus === 'submitted';
    });

    // Step 1: Head Approval should notify Finance (Step 2)
    $this->approvalService->processApprovalStep($approval, $this->head, 'approve');

    Mail::assertSent(\App\Mail\AtkStockRequestMail::class, function ($mail) {
        return $mail->hasTo($this->finance->email) && $mail->actionStatus === 'partially_approved';
    });
});

it('dispatches system notification to the next approver', function () {
    // Initial creation should notify initial approver (Head)
    $approval = $this->approvalService->createApproval($this->stockRequest, AtkStockRequest::class);

    Notification::assertSentTo($this->head, \Filament\Notifications\DatabaseNotification::class, function ($notification) {
        return $notification->data['title'] === 'Permintaan Stok ATK Baru';
    });

    // Step 1: Head Approval should notify Finance (Step 2)
    $this->approvalService->processApprovalStep($approval, $this->head, 'approve');

    Notification::assertSentTo($this->finance, \Filament\Notifications\DatabaseNotification::class, function ($notification) {
        return $notification->data['title'] === 'Permintaan Stok ATK Menunggu Persetujuan Anda';
    });
});

it('returns a valid approval action', function () {
    $action = App\Filament\Actions\ApprovalAction::makeApprove();

    expect($action)->toBeInstanceOf(\Filament\Actions\Action::class);
    expect($action->getName())->toBe('approve');
    expect($action->getColor())->toBe('success');
    expect($action->getLabel())->toBe('Setujui Permintaan');
});

it('hides approval action from unauthorized users', function () {
    $action = App\Filament\Actions\ApprovalAction::makeApprove();

    // Create approval record
    $this->approvalService->createApproval($this->stockRequest, AtkStockRequest::class);

    // Head should see it
    $this->actingAs($this->head);
    expect($action->record($this->stockRequest)->isVisible())->toBeTrue();

    // Finance should not see it (current step is 1)
    $this->actingAs($this->finance);
    expect($action->record($this->stockRequest)->isVisible())->toBeFalse();
});

it('shows approval action to the correct approver at step 2', function () {
    $action = App\Filament\Actions\ApprovalAction::makeApprove();

    $approval = $this->approvalService->createApproval($this->stockRequest, AtkStockRequest::class);
    $this->approvalService->processApprovalStep($approval, $this->head, 'approve');

    $this->stockRequest->refresh();
    $this->stockRequest->load('approval');

    // Head should no longer see it
    $this->actingAs($this->head);
    expect($action->record($this->stockRequest)->isVisible())->toBeFalse();

    // Finance should now see it
    $this->actingAs($this->finance);
    expect($action->record($this->stockRequest)->isVisible())->toBeTrue();
});
