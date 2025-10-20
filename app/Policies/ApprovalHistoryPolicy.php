<?php

namespace App\Policies;

use App\Models\ApprovalHistory;
use App\Models\User;

class ApprovalHistoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view approval-history');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApprovalHistory $approvalHistory): bool
    {
        return $user->can('view approval-history');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create approval-history');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApprovalHistory $approvalHistory): bool
    {
        return $user->can('edit approval-history');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApprovalHistory $approvalHistory): bool
    {
        return $user->can('delete approval-history');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ApprovalHistory $approvalHistory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ApprovalHistory $approvalHistory): bool
    {
        return false;
    }
}
