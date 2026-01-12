<?php

use App\Filament\Resources\AtkItems\Pages\ListAtkItems;
use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\User;
use App\Models\UserDivision;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('dashboard'));

    $this->division = UserDivision::create(['name' => 'GA', 'initial' => 'GA']);
    $this->adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    $this->user = User::factory()->create(['division_id' => $this->division->id]);
    $this->user->assignRole($this->adminRole);

    $this->category = AtkCategory::create(['name' => 'Stationery', 'slug' => 'atk']);

    $this->actingAs($this->user);
});

it('shows correct Bahasa notification when creating ATK item', function () {
    Livewire::test(ListAtkItems::class)
        ->callAction('create', data: [
            'name' => 'New Pen',
            'category_id' => $this->category->id,
            'unit_of_measure' => 'pcs',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified('Item ATK berhasil dibuat');
});

it('shows correct Bahasa notification when updating ATK item', function () {
    $item = AtkItem::create([
        'name' => 'Old Pen',
        'slug' => 'old-pen',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);

    Livewire::test(ListAtkItems::class)
        ->callTableAction('edit', $item, [
            'name' => 'Updated Pen',
        ])
        ->assertHasNoTableActionErrors()
        ->assertNotified('Item ATK berhasil diperbarui');
});

it('shows correct Bahasa notification when deleting ATK item', function () {
    $item = AtkItem::create([
        'name' => 'To Delete',
        'slug' => 'to-delete',
        'category_id' => $this->category->id,
        'unit_of_measure' => 'pcs',
    ]);

    Livewire::test(ListAtkItems::class)
        ->callTableAction('delete', $item)
        ->assertNotified('Item ATK berhasil dihapus');
});
