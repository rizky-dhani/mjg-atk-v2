<?php

use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can assign multiple divisions to a user', function () {
    $user = User::factory()->create();
    $divisionA = UserDivision::create(['name' => 'Division A', 'initial' => 'A']);
    $divisionB = UserDivision::create(['name' => 'Division B', 'initial' => 'B']);

    $user->divisions()->attach([$divisionA->id, $divisionB->id]);

    expect($user->fresh()->divisions)->toHaveCount(2)
        ->and($user->fresh()->divisions->pluck('id'))->toContain($divisionA->id, $divisionB->id);
});

it('correctly identifies GA user from multiple divisions', function () {
    $user = User::factory()->create();
    $divisionGA = UserDivision::create(['name' => 'General Affairs', 'initial' => 'GA']);
    $divisionHR = UserDivision::create(['name' => 'Human Resources', 'initial' => 'HR']);

    $user->divisions()->attach([$divisionHR->id]);
    expect($user->isGA())->toBeFalse();

    $user->divisions()->attach([$divisionGA->id]);
    expect($user->fresh()->isGA())->toBeTrue();
});

it('correctly checks division membership', function () {
    $user = User::factory()->create();
    $divisionA = UserDivision::create(['name' => 'Division A', 'initial' => 'A']);
    $divisionB = UserDivision::create(['name' => 'Division B', 'initial' => 'B']);

    $user->divisions()->attach([$divisionA->id]);

    expect($user->belongsToDivision($divisionA))->toBeTrue()
        ->and($user->belongsToDivision($divisionB->id))->toBeFalse();
});
