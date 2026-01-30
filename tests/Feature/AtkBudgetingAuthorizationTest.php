<?php

namespace Tests\Feature;

use App\Filament\Resources\AtkBudgetings\Pages\CreateAtkBudgeting;
use App\Filament\Resources\AtkBudgetings\Pages\EditAtkBudgeting;
use App\Filament\Resources\AtkBudgetings\Pages\ListAtkBudgetings;
use App\Models\AtkBudgeting;
use Database\Factories\UserDivisionFactory;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Super Admin']);
    Role::create(['name' => 'Admin']);
    Role::create(['name' => 'Staff']);

    Filament::setCurrentPanel(Filament::getPanel('dashboard'));
});

test('unauthorized users cannot view budgeting list', function () {
    $user = UserFactory::new()->create(['has_changed_password' => true]);
    $user->assignRole('Staff');

    $this->actingAs($user)
        ->get(ListAtkBudgetings::getUrl())
        ->assertForbidden();
});

test('admin users can view budgeting list', function () {
    $user = UserFactory::new()->create(['has_changed_password' => true]);
    $user->assignRole('Admin');

    $this->actingAs($user)
        ->get(ListAtkBudgetings::getUrl())
        ->assertSuccessful();
});

test('admin can only edit budgets for their division', function () {
    $divisionA = UserDivisionFactory::new()->create();
    $divisionB = UserDivisionFactory::new()->create();

    $admin = UserFactory::new()->create(['has_changed_password' => true]);
    $admin->assignRole('Admin');
    $admin->divisions()->attach($divisionA);

    $budgetA = AtkBudgeting::create([
        'division_id' => $divisionA->id,
        'budget_amount' => 1000,
        'fiscal_year' => 2025,
    ]);

    $budgetB = AtkBudgeting::create([
        'division_id' => $divisionB->id,
        'budget_amount' => 2000,
        'fiscal_year' => 2025,
    ]);

    $this->actingAs($admin);

    // Can edit division A
    $this->get(EditAtkBudgeting::getUrl(['record' => $budgetA]))
        ->assertSuccessful();

    // Cannot edit division B
    $this->get(EditAtkBudgeting::getUrl(['record' => $budgetB]))
        ->assertForbidden();
});

test('division_id is automated for single-division admin', function () {
    $division = UserDivisionFactory::new()->create();
    $admin = UserFactory::new()->create(['has_changed_password' => true]);
    $admin->assignRole('Admin');
    $admin->divisions()->attach($division);

    $admin->refresh();

    $this->actingAs($admin);

    Livewire::test(CreateAtkBudgeting::class)
        ->set('data.division_id', $division->id)
        ->set('data.budget_amount', 5000)
        ->set('data.fiscal_year', 2025)
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('atk_budgetings', [
        'division_id' => $division->id,
        'budget_amount' => 5000,
        'fiscal_year' => 2025,
    ]);
});

test('multi-division admin can select from their divisions', function () {
    $divisionA = UserDivisionFactory::new()->create();
    $divisionB = UserDivisionFactory::new()->create();

    $admin = UserFactory::new()->create(['has_changed_password' => true]);
    $admin->assignRole('Admin');
    $admin->divisions()->attach([$divisionA->id, $divisionB->id]);

    $admin->refresh();

    $this->actingAs($admin);

    Livewire::test(CreateAtkBudgeting::class)
        ->set('data.division_id', $divisionB->id)
        ->set('data.budget_amount', 3000)
        ->set('data.fiscal_year', 2025)
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('atk_budgetings', [
        'division_id' => $divisionB->id,
        'budget_amount' => 3000,
    ]);
});

test('super admin can manage any division budget', function () {
    $division = UserDivisionFactory::new()->create();
    $superAdmin = UserFactory::new()->create(['has_changed_password' => true]);
    $superAdmin->assignRole('Super Admin');

    $budget = AtkBudgeting::create([
        'division_id' => $division->id,
        'budget_amount' => 1000,
        'fiscal_year' => 2025,
    ]);

    $this->actingAs($superAdmin);

    $this->get(EditAtkBudgeting::getUrl(['record' => $budget]))
        ->assertSuccessful();

    Livewire::test(CreateAtkBudgeting::class)
        ->set('data.division_id', $division->id)
        ->set('data.budget_amount', 4000)
        ->set('data.fiscal_year', 2026) // Use different year to avoid unique constraint
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('atk_budgetings', [
        'division_id' => $division->id,
        'budget_amount' => 4000,
        'fiscal_year' => 2026,
    ]);
});
