<?php

namespace App\Policies;

use App\Models\AtkBudgeting;
use App\Models\User;

class AtkBudgetingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin') || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkBudgeting $atkBudgeting): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasRole('Admin') && $user->belongsToDivision($atkBudgeting->division_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Admin') || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AtkBudgeting $atkBudgeting): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasRole('Admin') && $user->belongsToDivision($atkBudgeting->division_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AtkBudgeting $atkBudgeting): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasRole('Admin') && $user->belongsToDivision($atkBudgeting->division_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AtkBudgeting $atkBudgeting): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AtkBudgeting $atkBudgeting): bool
    {
        return $user->isSuperAdmin();
    }
}
