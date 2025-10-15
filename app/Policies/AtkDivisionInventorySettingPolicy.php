<?php

namespace App\Policies;

use App\Models\AtkDivisionInventorySetting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AtkDivisionInventorySettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any atk-division-inventory-setting');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkDivisionInventorySetting $atkDivisionInventorySetting): bool
    {
        return $user->can('view atk-division-inventory-setting');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create atk-division-inventory-setting');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AtkDivisionInventorySetting $atkDivisionInventorySetting): bool
    {
        return $user->can('edit atk-division-inventory-setting');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AtkDivisionInventorySetting $atkDivisionInventorySetting): bool
    {
        return $user->can('delete atk-division-inventory-setting');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AtkDivisionInventorySetting $atkDivisionInventorySetting): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AtkDivisionInventorySetting $atkDivisionInventorySetting): bool
    {
        return false;
    }
}
