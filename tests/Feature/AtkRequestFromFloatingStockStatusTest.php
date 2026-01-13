<?php

use App\Models\Approval;
use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowStep;
use App\Models\AtkRequestFromFloatingStock;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDivision;
use App\Services\ApprovalProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create divisions
    $this->marketing = UserDivision::create(['name' => 'Marketing', 'initial' => 'MKT']);
    $this->ga = UserDivision::create(['name' => 'General Affairs', 'initial' => 'GA']);

    // Create roles
    $this->divHeadRole = Role::create(['name' => 'Division Head', 'guard_name' => 'web']);
    $this->gaAdminRole = Role::create(['name' => 'GA Admin', 'guard_name' => 'web']);

    // Create users
    $this->requester = User::factory()->create(['division_id' => $this->marketing->id]);
    
    $this->divHead = User::factory()->create(['division_id' => $this->marketing->id]);
    $this->divHead->assignRole($this->divHeadRole);

    $this->gaAdmin = User::factory()->create(['division_id' => $this->ga->id]);
    $this->gaAdmin->assignRole($this->gaAdminRole);

    // Setup Approval Flow
    $this->flow = ApprovalFlow::create([
        'name' => 'ATK Request from Stock Umum',
        'model_type' => AtkRequestFromFloatingStock::class,
        'is_active' => true,
    ]);

    // Step 1: Division Head
    $this->step1 = ApprovalFlowStep::create([
        'flow_id' => $this->flow->id,
        'step_name' => 'Division Head',
        'step_number' => 1,
        'role_id' => $this->divHeadRole->id,
        'division_id' => null, // Matches requester's division
    ]);

    // Step 2: GA Admin
    $this->step2 = ApprovalFlowStep::create([
        'flow_id' => $this->flow->id,
        'step_name' => 'GA Admin',
        'step_number' => 2,
        'role_id' => $this->gaAdminRole->id,
        'division_id' => $this->ga->id,
    ]);
});

test('it updates status to approved after all steps are completed', function () {
    // Create request
    $request = AtkRequestFromFloatingStock::create([
        'request_number' => 'REQ-FLOAT-001',
        'requester_id' => $this->requester->id,
        'division_id' => $this->marketing->id,
    ]);

    // Ensure approval record is created
    $approval = $request->approval;
    expect($approval)->not->toBeNull();
    expect($approval->status)->toBe('pending');
    expect($request->approval_status)->toBe('pending');

    $service = app(ApprovalProcessingService::class);

    // 1. Division Head approves
    $service->processApprovalStep($approval, $this->divHead, 'approve');
    $request->refresh();
    $approval->refresh();

    expect($approval->current_step)->toBe(2);
    expect($approval->status)->toBe('pending'); // Still pending until final
    
    // 2. GA Admin approves (FINAL STEP)
    $service->processApprovalStep($approval, $this->gaAdmin, 'approve');
    $request->refresh();
    $approval->refresh();

    expect($approval->status)->toBe('approved');
    expect($request->approval_status)->toBe('approved');
});
