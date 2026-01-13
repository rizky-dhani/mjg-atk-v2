<?php

use App\Filament\Resources\AtkRequestFromFloatingStocks\Pages\ApprovalAtkRequestFromFloatingStock;
use App\Models\Approval;
use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowStep;
use App\Models\AtkRequestFromFloatingStock;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

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

    // Step 1: Division Head (null division_id means match requester's division)
    $this->step1 = ApprovalFlowStep::create([
        'flow_id' => $this->flow->id,
        'step_name' => 'Division Head',
        'step_number' => 1,
        'role_id' => $this->divHeadRole->id,
        'division_id' => null,
    ]);

    // Step 2: GA Admin (explicit division General Affairs)
    $this->step2 = ApprovalFlowStep::create([
        'flow_id' => $this->flow->id,
        'step_name' => 'GA Admin',
        'step_number' => 2,
        'role_id' => $this->gaAdminRole->id,
        'division_id' => $this->ga->id,
    ]);
});

test('GA Admin (Step 2) cannot see a request at Step 1', function () {
    // Create request
    $request = AtkRequestFromFloatingStock::create([
        'request_number' => 'REQ-001',
        'requester_id' => $this->requester->id,
        'division_id' => $this->marketing->id,
    ]);

    // Create approval record
    $approval = Approval::create([
        'approvable_type' => AtkRequestFromFloatingStock::class,
        'approvable_id' => $request->id,
        'flow_id' => $this->flow->id,
        'current_step' => 1,
        'status' => 'pending',
    ]);

    $request->refresh();

    // Acting as GA Admin
    $this->actingAs($this->gaAdmin);

    // Verify it's NOT in the approval list
    Livewire::test(ApprovalAtkRequestFromFloatingStock::class)
        ->assertCanNotSeeTableRecords([$request]);
});

test('Division Head (Step 1) CAN see a request at Step 1', function () {
    // Create request
    $request = AtkRequestFromFloatingStock::create([
        'request_number' => 'REQ-001',
        'requester_id' => $this->requester->id,
        'division_id' => $this->marketing->id,
    ]);

    // Create approval record
    $approval = Approval::create([
        'approvable_type' => AtkRequestFromFloatingStock::class,
        'approvable_id' => $request->id,
        'flow_id' => $this->flow->id,
        'current_step' => 1,
        'status' => 'pending',
    ]);

    $request->refresh();

    // Acting as Division Head
    $this->actingAs($this->divHead);

    // Verify it IS in the approval list
    Livewire::test(ApprovalAtkRequestFromFloatingStock::class)
        ->assertCanSeeTableRecords([$request]);
});

test('GA Admin (Step 2) CAN see a request once it reaches Step 2', function () {
    // Create request
    $request = AtkRequestFromFloatingStock::create([
        'request_number' => 'REQ-001',
        'requester_id' => $this->requester->id,
        'division_id' => $this->marketing->id,
    ]);

    // Update auto-created approval record to Step 2
    $request->approval->update([
        'current_step' => 2,
    ]);

    $request->refresh();

    // Acting as GA Admin
    $this->actingAs($this->gaAdmin);

    // Verify it IS in the approval list
    Livewire::test(ApprovalAtkRequestFromFloatingStock::class)
        ->assertCanSeeTableRecords([$request]);
});

test('Division Head (Step 1) no longer sees the request after it moves to Step 2', function () {
    // Create request
    $request = AtkRequestFromFloatingStock::create([
        'request_number' => 'REQ-001',
        'requester_id' => $this->requester->id,
        'division_id' => $this->marketing->id,
    ]);

    // Update auto-created approval record to Step 2
    $request->approval->update([
        'current_step' => 2,
    ]);

    $request->refresh();

    // Acting as Division Head
    $this->actingAs($this->divHead);

    // Verify it's NOT in the approval list anymore
    Livewire::test(ApprovalAtkRequestFromFloatingStock::class)
        ->assertCanNotSeeTableRecords([$request]);
});
