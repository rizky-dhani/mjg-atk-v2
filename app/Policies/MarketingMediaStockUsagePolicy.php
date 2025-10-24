<?php

namespace App\Policies;

use App\Models\ApprovalFlow;
use App\Models\MarketingMediaStockUsage;
use App\Models\User;

class MarketingMediaStockUsagePolicy
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

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockUsage model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockUsage::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                if ($user->hasRole($step->role) &&
                    (! $step->division_id || $user->division_id == $step->division_id)) {
                    return true;
                }
            }
        }

        // Allow users with "Admin Marketing" role or regular admins,
        // plus users from any division with "Marketing" in the name (fallback)
        return $user->division && stripos($user->division->name, 'Marketing') !== false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MarketingMediaStockUsage $marketingMediaStockUsage): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'marketing') !== false) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockUsage model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockUsage::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                if ($user->hasRole($step->role) &&
                    (! $step->division_id || $user->division_id == $step->division_id)) {
                    return true;
                }
            }
        }

        // Allow users with "Admin Marketing" role or regular admins to view any usage (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Allow users from a marketing division to view usages from the same division
        return $user->division &&
               stripos($user->division->name, 'Marketing') !== false &&
               $user->division->id === $marketingMediaStockUsage->division_id;
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

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockUsage model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockUsage::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                if ($user->hasRole($step->role) &&
                    (! $step->division_id || $user->division_id == $step->division_id)) {
                    return true;
                }
            }
        }

        // Allow users with "Admin Marketing" role or regular admins to create usages (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Allow users from any division with "Marketing" in the name to create usages
        return $user->division && stripos($user->division->name, 'Marketing') !== false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MarketingMediaStockUsage $marketingMediaStockUsage): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'marketing') !== false) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockUsage model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockUsage::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                if ($user->hasRole($step->role) &&
                    (! $step->division_id || $user->division_id == $step->division_id)) {
                    return true;
                }
            }
        }

        // Allow users with "Admin Marketing" role or regular admins to update any usage (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Regular users can update their own pending usages if they're from a marketing division
        return $user->id === $marketingMediaStockUsage->requester_id &&
            $user->division &&
            stripos($user->division->name, 'Marketing') !== false &&
            $user->division->id === $marketingMediaStockUsage->division_id &&
            $marketingMediaStockUsage->approval &&
            $marketingMediaStockUsage->approval->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MarketingMediaStockUsage $marketingMediaStockUsage): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'Marketing') !== false) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockUsage model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockUsage::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                if ($user->hasRole($step->role) &&
                    (! $step->division_id || $user->division_id == $step->division_id)) {
                    return true;
                }
            }
        }

        // Allow users with "Admin Marketing" role or regular admins to delete any usage (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Regular users can delete their own pending usages if they're from a marketing division
        return $user->id === $marketingMediaStockUsage->requester_id &&
            $user->division &&
            stripos($user->division->name, 'Marketing') !== false &&
            $user->division->id === $marketingMediaStockUsage->division_id &&
            $marketingMediaStockUsage->approval &&
            $marketingMediaStockUsage->approval->status === 'pending';
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, MarketingMediaStockUsage $marketingMediaStockUsage): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'marketing') !== false) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockUsage model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockUsage::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                if ($user->hasRole($step->role) &&
                    (! $step->division_id || $user->division_id == $step->division_id)) {
                    // Check if this step is for the current approval's next step
                    if (! $marketingMediaStockUsage->approval ||
                        $marketingMediaStockUsage->approval->current_step == $step->step_number) {
                        return true;
                    }
                }
            }
        }

        // Allow users with "Admin Marketing" role to approve any usage (fallback)
        if ($user->hasRole('Admin Marketing')) {
            return true;
        }

        // Check if the user is an approver from ApprovalFlowStep of the corresponding ApprovalFlow
        if (! $marketingMediaStockUsage->approval || ! $marketingMediaStockUsage->approval->approvalFlow) {
            return false;
        }

        // Get the approval flow steps for this usage's approval flow
        $approvalFlowSteps = $marketingMediaStockUsage->approval->approvalFlow->approvalFlowSteps;

        foreach ($approvalFlowSteps as $step) {
            // Check if the user has the role required for this step and belongs to the right division
            if ($user->hasRole($step->role) &&
                (! $step->division_id || $user->division_id == $step->division_id)) {
                // Check if this step is for the current approval's next step
                if ($marketingMediaStockUsage->approval->current_step == $step->step_number) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine whether the user can resubmit the model after rejected.
     */
    public function resubmit(User $user, MarketingMediaStockUsage $marketingMediaStockUsage): bool
    {
        // Allow users with "Admin Marketing" role to resubmit any usage
        if ($user->hasRole('Admin Marketing')) {
            return true;
        }

        // Regular users can resubmit their own rejected usages if they're from a marketing division
        return $user->id === $marketingMediaStockUsage->requester_id &&
            $user->division &&
            stripos($user->division->name, 'Marketing') !== false &&
            $user->division->id === $marketingMediaStockUsage->division_id &&
            $marketingMediaStockUsage->approval &&
            $marketingMediaStockUsage->approval->status === 'rejected';
    }
}
