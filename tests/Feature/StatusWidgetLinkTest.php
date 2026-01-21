<?php

namespace Tests\Feature;

use App\Filament\Widgets\AtkStockRequestStatus;
use App\Filament\Widgets\AtkStockUsageStatus;
use App\Filament\Widgets\AtkTransferStockStatus;
use App\Models\User;
use App\Models\UserDivision;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('dashboard'));

    $this->division = UserDivision::create(['name' => 'IT', 'initial' => 'IT']);
    $this->user = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'initial' => 'SA',
        'division_id' => $this->division->id,
    ]);
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);
});

it('AtkStockRequestStatus widget has correct links', function () {
    $widget = new AtkStockRequestStatus;
    $method = new ReflectionMethod(AtkStockRequestStatus::class, 'getStats');
    $method->setAccessible(true);
    $stats = $method->invoke($widget);

    expect($stats[0]->getUrl())->toContain('tableFilters%5Bapproval_status%5D%5Bvalue%5D=pending');
    expect($stats[1]->getUrl())->toContain('tableFilters%5Bapproval_status%5D%5Bvalue%5D=partially_approved');
    expect($stats[2]->getUrl())->toContain('tableFilters%5Bapproval_status%5D%5Bvalue%5D=approved');
});

it('AtkStockUsageStatus widget has correct links', function () {
    $widget = new AtkStockUsageStatus;
    $method = new ReflectionMethod(AtkStockUsageStatus::class, 'getStats');
    $method->setAccessible(true);
    $stats = $method->invoke($widget);

    expect($stats[0]->getUrl())->toContain('tableFilters%5Bapproval_status%5D%5Bvalue%5D=pending');
    expect($stats[1]->getUrl())->toContain('tableFilters%5Bapproval_status%5D%5Bvalue%5D=partially_approved');
    expect($stats[2]->getUrl())->toContain('tableFilters%5Bapproval_status%5D%5Bvalue%5D=approved');
});

it('AtkTransferStockStatus widget has correct links', function () {
    $widget = new AtkTransferStockStatus;
    $method = new ReflectionMethod(AtkTransferStockStatus::class, 'getStats');
    $method->setAccessible(true);
    $stats = $method->invoke($widget);

    expect($stats[0]->getUrl())->toContain('tableFilters%5Bstatus%5D%5Bvalue%5D=pending');
    expect($stats[1]->getUrl())->toContain('tableFilters%5Bstatus%5D%5Bvalue%5D=partially_approved');
    expect($stats[2]->getUrl())->toContain('tableFilters%5Bstatus%5D%5Bvalue%5D=approved');
});
