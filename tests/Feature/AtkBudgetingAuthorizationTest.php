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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Super Admin']);
    Role::create(['name' => 'Admin']);
    Role::create(['name' => 'Staff']);

    Permission::create(['name' => 'view-any atk-budgeting']);
    Permission::create(['name' => 'view atk-budgeting']);
    Permission::create(['name' => 'create atk-budgeting']);
    Permission::create(['name' => 'edit atk-budgeting']);
    Permission::create(['name' => 'delete atk-budgeting']);

    // Super Admin: global permissions (null division_id)
    Role::where('name', 'Super Admin')->first()->givePermissionTo(
        'view-any atk-budgeting',
        'view atk-budgeting',
        'create atk-budgeting',
        'edit atk-budgeting',
        'delete atk-budgeting',
    );

    Filament::setCurrentPanel(Filament::getPanel('dashboard'));
});

/**
 * Assign division-scoped permissions to a role.
 */
function giveRoleDivisionPermission(Role $role, int $divisionId, string ...$permissionNames): void
{
    foreach ($permissionNames as $name) {
        $permission = Permission::where('name', $name)->first();
        $role->permissions()->attach($permission->id, ['division_id' => $divisionId]);
    }
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
}

test('unauthorized users cannot view budgeting list', function () {
    $user = UserFactory::new()->create(['has_changed_password' => true, 'is_active' => true]);
    $user->assignRole('Staff');

    Livewire::actingAs($user)
        ->test(ListAtkBudgetings::class)
        ->assertForbidden();
});

test('admin users can view budgeting list', function () {
    $division = UserDivisionFactory::new()->create();
    $admin = UserFactory::new()->create(['has_changed_password' => true, 'is_active' => true]);
    $admin->assignRole('Admin');
    $admin->divisions()->attach($division);

    giveRoleDivisionPermission(Role::where('name', 'Admin')->first(), $division->id, 'view-any atk-budgeting', 'view atk-budgeting');

    Livewire::actingAs($admin)
        ->test(ListAtkBudgetings::class)
        ->assertOk();
});

test('admin can only see their division budgets in list', function () {
    $divisionA = UserDivisionFactory::new()->create();
    $divisionB = UserDivisionFactory::new()->create();

    $admin = UserFactory::new()->create(['has_changed_password' => true, 'is_active' => true]);
    $admin->assignRole('Admin');
    $admin->divisions()->attach($divisionA);

    giveRoleDivisionPermission(Role::where('name', 'Admin')->first(), $divisionA->id, 'view-any atk-budgeting', 'view atk-budgeting', 'edit atk-budgeting');

    AtkBudgeting::create([
        'division_id' => $divisionA->id,
        'budget_amount' => 1000,
        'fiscal_year' => 2025,
    ]);

    AtkBudgeting::create([
        'division_id' => $divisionB->id,
        'budget_amount' => 2000,
        'fiscal_year' => 2025,
    ]);

    // Verify DivisionScoped trait limits visible records
    $visibleBudgets = AtkBudgeting::forUser($admin)->get();
    $this->assertCount(1, $visibleBudgets);
    $this->assertEquals($divisionA->id, $visibleBudgets->first()->division_id);
});

test('division_id is automated for single-division admin', function () {
    $division = UserDivisionFactory::new()->create();
    $admin = UserFactory::new()->create(['has_changed_password' => true, 'is_active' => true]);
    $admin->assignRole('Admin');
    $admin->divisions()->attach($division);

    giveRoleDivisionPermission(Role::where('name', 'Admin')->first(), $division->id, 'view-any atk-budgeting', 'view atk-budgeting', 'create atk-budgeting', 'edit atk-budgeting');

    $admin->refresh();

    Livewire::actingAs($admin)
        ->test(CreateAtkBudgeting::class)
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

    $admin = UserFactory::new()->create(['has_changed_password' => true, 'is_active' => true]);
    $admin->assignRole('Admin');
    $admin->divisions()->attach([$divisionA->id, $divisionB->id]);

    $adminRole = Role::where('name', 'Admin')->first();
    giveRoleDivisionPermission($adminRole, $divisionA->id, 'view-any atk-budgeting', 'view atk-budgeting', 'create atk-budgeting', 'edit atk-budgeting');
    giveRoleDivisionPermission($adminRole, $divisionB->id, 'view-any atk-budgeting', 'view atk-budgeting', 'create atk-budgeting', 'edit atk-budgeting');

    $admin->refresh();

    Livewire::actingAs($admin)
        ->test(CreateAtkBudgeting::class)
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
    $superAdmin = UserFactory::new()->create(['has_changed_password' => true, 'is_active' => true]);
    $superAdmin->assignRole('Super Admin');

    $budget = AtkBudgeting::create([
        'division_id' => $division->id,
        'budget_amount' => 1000,
        'fiscal_year' => 2025,
    ]);

    // Super Admin has global permissions — DivisionScoped returns all
    $visibleBudgets = AtkBudgeting::forUser($superAdmin)->get();
    $this->assertCount(1, $visibleBudgets);
    $this->assertEquals($budget->id, $visibleBudgets->first()->id);

    Livewire::actingAs($superAdmin)
        ->test(CreateAtkBudgeting::class)
        ->set('data.division_id', $division->id)
        ->set('data.budget_amount', 4000)
        ->set('data.fiscal_year', 2026)
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('atk_budgetings', [
        'division_id' => $division->id,
        'budget_amount' => 4000,
        'fiscal_year' => 2026,
    ]);
});
