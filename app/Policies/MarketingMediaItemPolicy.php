<?php

namespace App\Policies;

use App\Models\ApprovalFlow;
use App\Models\MarketingMediaItem;
use App\Models\User;

class MarketingMediaItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Check for Super Admin and Admin from GA roles
        if ($user->hasRole('Super Admin') || ($user->hasRole('Admin') && $user->isGA())) {
            return true;
        }

        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->divisions()->where('name', 'like', '%Marketing%')->exists()) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaItem model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaItem::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                // Check if the user has the role specified in the step and matches the division
                if ($user->hasRole($step->role->name) &&
                    (! $step->division_id || $user->belongsToDivision($step->division_id))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MarketingMediaItem $marketingMediaItem): bool
    {
        // Check for Super Admin and Admin from GA roles
        if ($user->hasRole('Super Admin') || ($user->hasRole('Admin') && $user->isGA())) {
            return true;
        }

        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->divisions()->where('name', 'like', '%Marketing%')->exists()) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaItem model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaItem::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                // Check if the user has the role specified in the step and matches the division
                if ($user->hasRole($step->role->name) &&
                    (! $step->division_id || $user->belongsToDivision($step->division_id))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Check for Super Admin and Admin from GA roles
        if ($user->hasRole('Super Admin') || ($user->hasRole('Admin') && $user->isGA())) {
            return true;
        }

        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->divisions()->where('name', 'like', '%Marketing%')->exists()) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaItem model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaItem::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                // Check if the user has the role specified in the step and matches the division
                if ($user->hasRole($step->role->name) &&
                    (! $step->division_id || $user->belongsToDivision($step->division_id))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MarketingMediaItem $marketingMediaItem): bool
    {
        // Check for Super Admin and Admin from GA roles
        if ($user->hasRole('Super Admin') || ($user->hasRole('Admin') && $user->isGA())) {
            return true;
        }

        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->divisions()->where('name', 'like', '%Marketing%')->exists()) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaItem model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaItem::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                // Check if the user has the role specified in the step and matches the division
                if ($user->hasRole($step->role->name) &&
                    (! $step->division_id || $user->belongsToDivision($step->division_id))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MarketingMediaItem $marketingMediaItem): bool
    {
        // Check for Super Admin and Admin from GA roles
        if ($user->hasRole('Super Admin') || ($user->hasRole('Admin') && $user->isGA())) {
            return true;
        }

        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->divisions()->where('name', 'like', '%Marketing%')->exists()) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaItem model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaItem::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                // Check if the user has the role specified in the step and matches the division
                if ($user->hasRole($step->role->name) &&
                    (! $step->division_id || $user->belongsToDivision($step->division_id))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine whether the user can manage items.
     */
    public function manageItems(User $user): bool
    {
        // Check for Super Admin and Admin from GA roles
        if ($user->hasRole('Super Admin') || ($user->hasRole('Admin') && $user->isGA())) {
            return true;
        }

        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->divisions()->where('name', 'like', '%Marketing%')->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MarketingMediaItem $marketingMediaItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MarketingMediaItem $marketingMediaItem): bool
    {
        return false;
    }
}
