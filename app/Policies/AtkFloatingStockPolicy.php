<?php

namespace App\Policies;

use App\Models\AtkFloatingStock;
use App\Models\User;

class AtkFloatingStockPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isGA();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkFloatingStock $atkFloatingStock): bool
    {
        return $user->isGA();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isGA();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AtkFloatingStock $atkFloatingStock): bool
    {
        return $user->isGA();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AtkFloatingStock $atkFloatingStock): bool
    {
        return $user->isGA();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AtkFloatingStock $atkFloatingStock): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AtkFloatingStock $atkFloatingStock): bool
    {
        return $user->isSuperAdmin();
    }
}
