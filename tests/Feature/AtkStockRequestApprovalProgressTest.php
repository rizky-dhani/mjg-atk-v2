<?php

use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowStep;
use App\Models\AtkStockRequest;
use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->division = UserDivision::create([
        'name' => 'IT Division',
        'initial' => 'IT',
    ]);

    $this->financeDivision = UserDivision::create([
        'name' => 'Finance Division',
        'initial' => 'FIN',
    ]);

    $this->headRole = Role::create(['name' => 'Head', 'guard_name' => 'web']);
    $this->financeRole = Role::create(['name' => 'Finance', 'guard_name' => 'web']);

    $this->staff = User::factory()->create([
        'division_id' => $this->division->id,
    ]);

    $this->head = User::factory()->create([
        'name' => 'John Head',
        'division_id' => $this->division->id,
    ]);
    $this->head->assignRole($this->headRole);

    $this->financeUser = User::factory()->create([
        'name' => 'Jane Finance',
        'division_id' => $this->financeDivision->id,
    ]);
    $this->financeUser->assignRole($this->financeRole);

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
        'division_id' => null,
    ]);

    $this->step2 = ApprovalFlowStep::create([
        'flow_id' => $this->flow->id,
        'step_name' => 'Finance Approval',
        'step_number' => 2,
        'role_id' => $this->financeRole->id,
        'division_id' => $this->financeDivision->id,
    ]);

    $this->stockRequest = AtkStockRequest::create([
        'request_number' => 'REQ-001',
        'requester_id' => $this->staff->id,
        'division_id' => $this->division->id,
        'request_type' => 'addition',
    ]);
});

it('returns correct potential approvers for each step', function () {
    $approval = $this->stockRequest->approval;
    $progress = $approval->getApprovalProgress();

    expect($progress)->toHaveCount(2);

    // Step 1
    $step1Progress = $progress->firstWhere('step_number', 1);
    expect($step1Progress['step_name'])->toBe('Division Head Approval');
    expect($step1Progress['potential_approvers'])->toHaveCount(1);
    expect($step1Progress['potential_approvers']->first()->name)->toBe('John Head');
    expect($step1Progress['status'])->toBe('pending');

    // Step 2
    $step2Progress = $progress->firstWhere('step_number', 2);
    expect($step2Progress['step_name'])->toBe('Finance Approval');
    expect($step2Progress['potential_approvers'])->toHaveCount(1);
    expect($step2Progress['potential_approvers']->first()->name)->toBe('Jane Finance');
    expect($step2Progress['status'])->toBe('waiting');
});

it('updates status in progress as steps are approved', function () {
    $approval = $this->stockRequest->approval;

    // Approve step 1
    app(\App\Services\ApprovalProcessingService::class)->processApprovalStep($approval, $this->head, 'approve');

    $progress = $approval->refresh()->getApprovalProgress();

    $step1Progress = $progress->firstWhere('step_number', 1);
    expect($step1Progress['status'])->toBe('approved');
    expect($step1Progress['approver_name'])->toBe('John Head');

    $step2Progress = $progress->firstWhere('step_number', 2);
    expect($step2Progress['status'])->toBe('pending');
});

it('shows blocked status for remaining steps when rejected', function () {
    $approval = $this->stockRequest->approval;

    // Reject step 1
    app(\App\Services\ApprovalProcessingService::class)->processApprovalStep($approval, $this->head, 'reject', 'No thanks');

    $progress = $approval->refresh()->getApprovalProgress();

    $step1Progress = $progress->firstWhere('step_number', 1);
    expect($step1Progress['status'])->toBe('rejected');

    $step2Progress = $progress->firstWhere('step_number', 2);
    expect($step2Progress['status'])->toBe('blocked');
});
