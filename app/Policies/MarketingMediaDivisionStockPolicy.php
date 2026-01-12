<?php

namespace App\Policies;

use App\Models\MarketingMediaDivisionStock;
use App\Models\User;

class MarketingMediaDivisionStockPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'Marketing') !== false) {
            return true;
        }

        // Allow users with "Admin Marketing" role or regular admins,
        // plus users from any division with "Marketing" in the name (fallback)
        return $user->division && stripos($user->division->name, 'Marketing') !== false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MarketingMediaDivisionStock $marketingMediaDivisionStock): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'marketing') !== false) {
            return true;
        }

        // Allow users with "Admin Marketing" role or regular admins to view any stock (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Allow users from the same division to view stocks
        return $user->division &&
               $user->division->id === $marketingMediaDivisionStock->division_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'marketing') !== false) {
            return true;
        }

        // Allow users with "Admin Marketing" role or regular admins to create stocks (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Allow users from any division with "Marketing" in the name to create stocks
        return $user->division && stripos($user->division->name, 'Marketing') !== false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MarketingMediaDivisionStock $marketingMediaDivisionStock): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'marketing') !== false) {
            return true;
        }

        // Allow users with "Admin Marketing" role or regular admins to update any stock (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Allow users from the same division to update stocks
        return $user->division &&
               $user->division->id === $marketingMediaDivisionStock->division_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MarketingMediaDivisionStock $marketingMediaDivisionStock): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'Marketing') !== false) {
            return true;
        }

        // Allow users with "Admin Marketing" role or regular admins to delete any stock (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Allow users from the same division to delete stocks
        return $user->division &&
               $user->division->id === $marketingMediaDivisionStock->division_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MarketingMediaDivisionStock $marketingMediaDivisionStock): bool
    {
        // Not implemented for now
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MarketingMediaDivisionStock $marketingMediaDivisionStock): bool
    {
        // Not implemented for now
        return false;
    }
}
