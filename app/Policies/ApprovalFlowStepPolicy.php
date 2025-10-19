<?php

namespace App\Policies;

use App\Models\ApprovalFlowStep;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ApprovalFlowStepPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any approval-flow-step');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApprovalFlowStep $approvalFlowStep): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApprovalFlowStep $approvalFlowStep): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApprovalFlowStep $approvalFlowStep): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ApprovalFlowStep $approvalFlowStep): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ApprovalFlowStep $approvalFlowStep): bool
    {
        return false;
    }
}
