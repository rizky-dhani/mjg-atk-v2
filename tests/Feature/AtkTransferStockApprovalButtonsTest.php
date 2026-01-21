<?php

namespace Tests\Feature;

use App\Filament\Resources\AtkTransferStocks\Pages\ViewAtkTransferStock;
use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowStep;
use App\Models\AtkTransferStock;
use App\Models\User;
use App\Models\UserDivision;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\PermissionSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('dashboard'));

    $this->division1 = UserDivision::create(['name' => 'IT', 'initial' => 'IT']);
    $this->division2 = UserDivision::create(['name' => 'GA', 'initial' => 'GA']);

    $this->adminRole = Role::where('name', 'Admin')->first();

    $this->user = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'initial' => 'SA',
        'division_id' => $this->division1->id,
    ]);
    $this->user->assignRole(['Super Admin', 'Admin']);

    $this->actingAs($this->user);

    // Setup Approval Flow with 2 steps to test partial approval
    $this->flow = ApprovalFlow::create([
        'name' => 'Transfer Flow',
        'model_type' => AtkTransferStock::class,
        'is_active' => true,
    ]);

    $this->step1 = ApprovalFlowStep::create([
        'flow_id' => $this->flow->id,
        'step_name' => 'Step 1',
        'step_number' => 1,
        'role_id' => $this->adminRole->id,
        'division_id' => $this->division1->id,
    ]);

    $this->step2 = ApprovalFlowStep::create([
        'flow_id' => $this->flow->id,
        'step_name' => 'Step 2',
        'step_number' => 2,
        'role_id' => $this->adminRole->id,
        'division_id' => $this->division2->id,
    ]);
});

it('shows approval buttons for authorized user on view page', function () {
    $transfer = AtkTransferStock::create([
        'transfer_number' => 'TRF-001',
        'requester_id' => $this->user->id,
        'requesting_division_id' => $this->division1->id,
        'source_division_id' => $this->division2->id,
    ]);

    Livewire::test(ViewAtkTransferStock::class, ['record' => $transfer->id])
        ->assertStatus(200)
        ->assertActionVisible('approve_request')
        ->assertActionVisible('reject_request');
});

it('can partially approve a transfer via view page', function () {
    $transfer = AtkTransferStock::create([
        'transfer_number' => 'TRF-003',
        'requester_id' => $this->user->id,
        'requesting_division_id' => $this->division1->id,
        'source_division_id' => $this->division2->id,
    ]);

    Livewire::test(ViewAtkTransferStock::class, ['record' => $transfer->id])
        ->callAction('approve_request');

    $transfer->refresh();
    expect($transfer->approval->status)->toBe('pending');
    expect($transfer->approval->current_step)->toBe(2);
});

it('can reject a transfer via view page', function () {
    $transfer = AtkTransferStock::create([
        'transfer_number' => 'TRF-REJECT',
        'requester_id' => $this->user->id,
        'requesting_division_id' => $this->division1->id,
        'source_division_id' => $this->division2->id,
    ]);

    Livewire::test(ViewAtkTransferStock::class, ['record' => $transfer->id])
        ->mountAction('reject_request')
        ->setActionData([
            'rejection_notes' => 'Rejected test',
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    $transfer->refresh();
    expect($transfer->approval->status)->toBe('rejected');
});
