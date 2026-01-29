<?php

use App\Models\ApprovalFlow;
use App\Models\AtkStockRequest;
use App\Models\User;
use App\Models\UserDivision;
use App\Services\ApprovalValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->division = UserDivision::factory()->create(['name' => 'IT', 'initial' => 'IT']);
    $this->role = Role::create(['name' => 'Manager']);

    $this->pinnedUser = User::factory()->create(['name' => 'Pinned User']);
    $this->pinnedUser->assignRole($this->role);
    $this->pinnedUser->divisions()->attach($this->division);

    $this->otherUser = User::factory()->create(['name' => 'Other User']);
    $this->otherUser->assignRole($this->role);
    $this->otherUser->divisions()->attach($this->division);

    $this->flow = ApprovalFlow::create([
        'name' => 'Test Flow',
        'model_type' => AtkStockRequest::class,
        'is_active' => true,
    ]);

    $this->validationService = app(ApprovalValidationService::class);
});

it('only allows the pinned user to approve', function () {
    $step = $this->flow->approvalFlowSteps()->create([
        'step_name' => 'Step 1',
        'step_number' => 1,
        'role_id' => $this->role->id,
        'division_id' => $this->division->id,
        'user_id' => $this->pinnedUser->id,
    ]);

    $request = AtkStockRequest::factory()->create([
        'division_id' => $this->division->id,
        'requester_id' => User::factory()->create()->id,
    ]);

    // Pinned user should be able to approve
    expect($this->validationService->canUserApprove($request, $this->pinnedUser))->toBeTrue();

    // Other user with same role and division should NOT be able to approve
    expect($this->validationService->canUserApprove($request, $this->otherUser))->toBeFalse();
});

it('allows any user with matching role and division to approve if no user is pinned', function () {
    $step = $this->flow->approvalFlowSteps()->create([
        'step_name' => 'Step 1',
        'step_number' => 1,
        'role_id' => $this->role->id,
        'division_id' => $this->division->id,
        'user_id' => null,
    ]);

    $request = AtkStockRequest::factory()->create([
        'division_id' => $this->division->id,
        'requester_id' => User::factory()->create()->id,
    ]);

    // Both users should be able to approve
    expect($this->validationService->canUserApprove($request, $this->pinnedUser))->toBeTrue();
    expect($this->validationService->canUserApprove($request, $this->otherUser))->toBeTrue();
});

it('correctly identifies next approvers when pinned', function () {
    $step = $this->flow->approvalFlowSteps()->create([
        'step_name' => 'Step 1',
        'step_number' => 1,
        'role_id' => $this->role->id,
        'division_id' => $this->division->id,
        'user_id' => $this->pinnedUser->id,
    ]);

    $request = AtkStockRequest::factory()->create([
        'division_id' => $this->division->id,
        'requester_id' => User::factory()->create()->id,
    ]);

    $approval = $request->approval()->create([
        'flow_id' => $this->flow->id,
        'current_step' => 1,
        'status' => 'pending',
    ]);

    $processingService = app(\App\Services\ApprovalProcessingService::class);
    $reflection = new ReflectionClass($processingService);
    $method = $reflection->getMethod('getNextApprovers');
    $method->setAccessible(true);

    $approvers = $method->invoke($processingService, $approval);

    expect($approvers->count())->toBe(1);
    expect($approvers->first()->id)->toBe($this->pinnedUser->id);
});
