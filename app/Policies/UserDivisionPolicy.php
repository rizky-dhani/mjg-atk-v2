<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Auth\Access\Response;

class UserDivisionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any user-division');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserDivision $userDivision): bool
    {
        return $user->can('view user-division');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create user-division');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserDivision $userDivision): bool
    {
        return $user->can('edit user-division');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserDivision $userDivision): bool
    {
        return $user->can('delete user-division');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserDivision $userDivision): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserDivision $userDivision): bool
    {
        return false;
    }
}
