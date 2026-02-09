<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDivision;
use App\Notifications\TestNotification;
use Filament\Notifications\Livewire\DatabaseNotifications;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $division = UserDivision::create(['name' => 'IT', 'initial' => 'IT']);
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'initial' => 'TU',
        'division_id' => $division->id,
    ]);
});

it('can mark a notification as read', function () {
    $this->user->notify(new TestNotification);

    $this->actingAs($this->user);

    $dbNotification = $this->user->notifications()->first();
    expect($dbNotification)->not->toBeNull();
    expect($dbNotification->read_at)->toBeNull();

    Livewire::test(DatabaseNotifications::class)
        ->call('markAsRead', $dbNotification->id);

    expect($dbNotification->refresh()->read_at)->not->toBeNull();
});

it('can mark all notifications as read', function () {
    $this->user->notify(new TestNotification);
    $this->user->notify(new TestNotification);

    $this->actingAs($this->user);

    expect($this->user->unreadNotifications()->count())->toBe(2);

    Livewire::test(DatabaseNotifications::class)
        ->call('markAllAsRead');

    expect($this->user->unreadNotifications()->count())->toBe(0);
});
