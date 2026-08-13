<?php

namespace App\Policies;

use App\Models\AtkItem;
use App\Models\User;

class AtkItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any atk-item');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkItem $atkItem): bool
    {
        return $user->can('view atk-item');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create atk-item');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AtkItem $atkItem): bool
    {
        return $user->can('edit atk-item');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AtkItem $atkItem): bool
    {
        return $user->can('delete atk-item');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AtkItem $atkItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AtkItem $atkItem): bool
    {
        return false;
    }
}
