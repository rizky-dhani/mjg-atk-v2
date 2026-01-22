<?php

namespace App\Policies;

use App\Models\AtkFulfillment;
use App\Models\User;

class AtkFulfillmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ($user->division?->initial === 'IPC' || $user->isSuperAdmin()) && 
               $user->can('view-any atk-fulfillment');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkFulfillment $atkFulfillment): bool
    {
        return ($user->division?->initial === 'IPC' || $user->isSuperAdmin()) && 
               $user->can('view atk-fulfillment');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // Fulfillment records are created by AtkStockRequest flow
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AtkFulfillment $atkFulfillment): bool
    {
        return ($user->division?->initial === 'IPC' || $user->isSuperAdmin()) && 
               $user->can('edit atk-fulfillment') &&
               $atkFulfillment->approval?->status === 'approved';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AtkFulfillment $atkFulfillment): bool
    {
        return false;
    }
}
