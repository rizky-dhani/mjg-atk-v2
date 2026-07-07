<?php

use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->division = UserDivision::create(['name' => 'IT', 'initial' => 'IT']);
    Role::create(['name' => 'Super Admin']);
});

it('sends database notification to super admins on exception', function () {
    $superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'super@example.com',
        'password' => bcrypt('password'),
        'initial' => 'SA',
        'division_id' => $this->division->id,
    ]);
    $superAdmin->assignRole('Super Admin');

    $regularUser = User::create([
        'name' => 'Regular User',
        'email' => 'regular@example.com',
        'password' => bcrypt('password'),
        'initial' => 'RU',
        'division_id' => $this->division->id,
    ]);

    Route::get('/test-error-notify', function () {
        throw new RuntimeException('Notify test');
    });

    $this->actingAs($regularUser)->get('/test-error-notify');

    expect($superAdmin->notifications()->count())->toBe(1);
    expect($regularUser->notifications()->count())->toBe(0);

    $notification = $superAdmin->notifications()->first();
    expect($notification->data['title'])->toContain('RuntimeException');
    expect($notification->data['body'])->toContain('Notify test');
});

it('does not send database notification for excluded exceptions', function () {
    $superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'super@example.com',
        'password' => bcrypt('password'),
        'initial' => 'SA',
        'division_id' => $this->division->id,
    ]);
    $superAdmin->assignRole('Super Admin');

    $this->get('/nonexistent-page-'.uniqid());

    expect($superAdmin->notifications()->count())->toBe(0);
});

it('sends notification for different exceptions independently', function () {
    $superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'super@example.com',
        'password' => bcrypt('password'),
        'initial' => 'SA',
        'division_id' => $this->division->id,
    ]);
    $superAdmin->assignRole('Super Admin');

    Route::get('/test-error-a', function () {
        throw new RuntimeException('Error A');
    });
    Route::get('/test-error-b', function () {
        throw new InvalidArgumentException('Error B');
    });

    $this->get('/test-error-a');
    $this->get('/test-error-b');

    expect($superAdmin->notifications()->count())->toBe(2);
});
