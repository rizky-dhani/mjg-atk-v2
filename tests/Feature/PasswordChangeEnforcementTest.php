<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('dashboard'));
});

it('redirects user to profile page if password has not been changed', function () {
    $user = User::factory()->create([
        'has_changed_password' => false,
    ]);

    actingAs($user)
        ->get(Filament::getPanel('dashboard')->getUrl())
        ->assertRedirect(Filament::getPanel('dashboard')->getProfileUrl());
});

it('does not redirect user if password has been changed', function () {
    $user = User::factory()->create([
        'has_changed_password' => true,
    ]);

    actingAs($user)
        ->get(Filament::getPanel('dashboard')->getUrl())
        ->assertSuccessful();
});

it('allows access to profile page even if password has not been changed', function () {
    $user = User::factory()->create([
        'has_changed_password' => false,
    ]);

    actingAs($user)
        ->get(Filament::getPanel('dashboard')->getProfileUrl())
        ->assertSuccessful();
});

it('allows logout even if password has not been changed', function () {
    $user = User::factory()->create([
        'has_changed_password' => false,
    ]);

    withoutMiddleware(VerifyCsrfToken::class)
        ->actingAs($user)
        ->post(Filament::getPanel('dashboard')->getLogoutUrl())
        ->assertRedirect();
});
