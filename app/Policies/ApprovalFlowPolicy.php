<?php

namespace App\Policies;

use App\Models\ApprovalFlow;
use App\Models\User;

class ApprovalFlowPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any approval-flow');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApprovalFlow $approvalFlow): bool
    {
        return $user->can('view approval-flow');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create approval-flow');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApprovalFlow $approvalFlow): bool
    {
        return $user->can('edit approval-flow');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApprovalFlow $approvalFlow): bool
    {
        return $user->can('delete approval-flow');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ApprovalFlow $approvalFlow): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ApprovalFlow $approvalFlow): bool
    {
        return false;
    }
}
