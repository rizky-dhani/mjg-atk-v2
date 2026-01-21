<?php

namespace Tests\Feature;

use App\Enums\AtkStockRequestStatus;
use App\Filament\Resources\AtkStockRequests\Pages\ApprovalAtkStockRequest;
use App\Models\AtkStockRequest;
use App\Models\User;
use App\Models\UserDivision;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\UserDivisionSeeder::class);

    Filament::setCurrentPanel(Filament::getPanel('dashboard'));

    $this->division = UserDivision::create(['name' => 'IT', 'initial' => 'IT']);

    $this->user = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'initial' => 'SA',
        'division_id' => $this->division->id,
    ]);
    $this->user->assignRole(['Super Admin', 'Admin']);

    $this->actingAs($this->user);
});

it('has status column in atk_stock_requests table', function () {
    $request = AtkStockRequest::create([
        'request_number' => 'REQ-TEST',
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
        'request_type' => 'regular',
        'status' => AtkStockRequestStatus::Draft,
    ]);

    expect($request->status)->toBe(AtkStockRequestStatus::Draft);
});

it('defaults status to draft', function () {
    $request = AtkStockRequest::create([
        'request_number' => 'REQ-TEST-2',
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
        'request_type' => 'regular',
    ]);

    expect($request->refresh()->status)->toBe(AtkStockRequestStatus::Draft);
});

it('can be published', function () {
    $request = AtkStockRequest::create([
        'request_number' => 'REQ-TEST-3',
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
        'request_type' => 'regular',
        'status' => AtkStockRequestStatus::Draft,
    ]);

    $request->update(['status' => AtkStockRequestStatus::Published]);

    expect($request->refresh()->status)->toBe(AtkStockRequestStatus::Published);
});

it('only shows published requests in approval page', function () {
    // Create an approval flow
    $flow = \App\Models\ApprovalFlow::create([
        'name' => 'Test Flow',
        'model_type' => AtkStockRequest::class,
        'is_active' => true,
    ]);

    $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin')->first();

    \App\Models\ApprovalFlowStep::create([
        'flow_id' => $flow->id,
        'step_name' => 'Step 1',
        'step_number' => 1,
        'role_id' => $adminRole->id,
    ]);

    // Draft request
    $draftRequest = AtkStockRequest::create([
        'request_number' => 'REQ-DRAFT',
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
        'request_type' => 'regular',
        'status' => AtkStockRequestStatus::Draft,
    ]);

    // Published request
    $publishedRequest = AtkStockRequest::create([
        'request_number' => 'REQ-PUBLISHED',
        'requester_id' => $this->user->id,
        'division_id' => $this->division->id,
        'request_type' => 'regular',
        'status' => AtkStockRequestStatus::Published,
    ]);

    // Create approval for published request
    app(\App\Services\ApprovalProcessingService::class)->createApproval($publishedRequest, AtkStockRequest::class);

    Livewire::test(ApprovalAtkStockRequest::class)

        ->assertCanSeeTableRecords([$publishedRequest])

        ->assertCanNotSeeTableRecords([$draftRequest]);

});
