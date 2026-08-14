<?php

use App\Models\Role;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

test('debug exception', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    Role::create(['name' => 'Super Admin']);
    Role::create(['name' => 'Admin']);
    Role::create(['name' => 'Staff']);

    Permission::create(['name' => 'view-any atk-budgeting']);
    Role::where('name', 'Admin')->first()->givePermissionTo('view-any atk-budgeting');

    Filament::setCurrentPanel(Filament::getPanel('dashboard'));

    $user = UserFactory::new()->create(['has_changed_password' => true, 'is_active' => true]);
    $user->assignRole('Admin');

    $this->withoutExceptionHandling();
    $this->actingAs($user);

    $response = $this->get(\App\Filament\Resources\AtkBudgetings\Pages\ListAtkBudgetings::getUrl());
    echo "Status: " . $response->getStatusCode() . PHP_EOL;
});
