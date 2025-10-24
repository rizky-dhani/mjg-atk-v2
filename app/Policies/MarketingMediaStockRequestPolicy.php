<?php

namespace App\Policies;

use App\Models\ApprovalFlow;
use App\Models\MarketingMediaStockRequest;
use App\Models\User;

class MarketingMediaStockRequestPolicy
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

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockRequest model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockRequest::class)->first();
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
    public function view(User $user, MarketingMediaStockRequest $marketingMediaStockRequest): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'marketing') !== false) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockRequest model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockRequest::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                if ($user->hasRole($step->role) &&
                    (! $step->division_id || $user->division_id == $step->division_id)) {
                    return true;
                }
            }
        }

        // Allow users with "Admin Marketing" role or regular admins to view any request (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Allow users from a marketing division to view requests from the same division
        return $user->division &&
               stripos($user->division->name, 'Marketing') !== false &&
               $user->division->id === $marketingMediaStockRequest->division_id;
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

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockRequest model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockRequest::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                if ($user->hasRole($step->role) &&
                    (! $step->division_id || $user->division_id == $step->division_id)) {
                    return true;
                }
            }
        }

        // Allow users with "Admin Marketing" role or regular admins to create requests (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Allow users from any division with "Marketing" in the name to create requests
        return $user->division && stripos($user->division->name, 'Marketing') !== false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MarketingMediaStockRequest $marketingMediaStockRequest): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'marketing') !== false) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockRequest model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockRequest::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                if ($user->hasRole($step->role) &&
                    (! $step->division_id || $user->division_id == $step->division_id)) {
                    return true;
                }
            }
        }

        // Allow users with "Admin Marketing" role or regular admins to update any request (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Regular users can update their own pending requests if they're from a marketing division
        return $user->id === $marketingMediaStockRequest->requester_id &&
            $user->division &&
            stripos($user->division->name, 'Marketing') !== false &&
            $user->division->id === $marketingMediaStockRequest->division_id &&
            $marketingMediaStockRequest->approval &&
            $marketingMediaStockRequest->approval->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MarketingMediaStockRequest $marketingMediaStockRequest): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'Marketing') !== false) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockRequest model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockRequest::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                if ($user->hasRole($step->role) &&
                    (! $step->division_id || $user->division_id == $step->division_id)) {
                    return true;
                }
            }
        }

        // Allow users with "Admin Marketing" role or regular admins to delete any request (fallback)
        if ($user->hasRole(['Admin Marketing', 'Admin'])) {
            return true;
        }

        // Regular users can delete their own pending requests if they're from a marketing division
        return $user->id === $marketingMediaStockRequest->requester_id &&
            $user->division &&
            stripos($user->division->name, 'Marketing') !== false &&
            $user->division->id === $marketingMediaStockRequest->division_id &&
            $marketingMediaStockRequest->approval &&
            $marketingMediaStockRequest->approval->status === 'pending';
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, MarketingMediaStockRequest $marketingMediaStockRequest): bool
    {
        // Check if user has 'admin' role and belongs to Marketing division
        if ($user->hasRole('Admin') && $user->division && stripos($user->division->name, 'marketing') !== false) {
            return true;
        }

        // Check if user matches any ApprovalFlowStep for MarketingMediaStockRequest model type
        $marketingMediaApprovalFlow = ApprovalFlow::where('model_type', MarketingMediaStockRequest::class)->first();
        if ($marketingMediaApprovalFlow) {
            $approvalFlowSteps = $marketingMediaApprovalFlow->approvalFlowSteps;

            foreach ($approvalFlowSteps as $step) {
                if ($user->hasRole($step->role) &&
                    (! $step->division_id || $user->division_id == $step->division_id)) {
                    // Check if this step is for the current approval's next step
                    if (! $marketingMediaStockRequest->approval ||
                        $marketingMediaStockRequest->approval->current_step == $step->step_number) {
                        return true;
                    }
                }
            }
        }

        // Allow users with "Admin Marketing" role to approve any request (fallback)
        if ($user->hasRole('Admin Marketing')) {
            return true;
        }

        // Check if the user is an approver from ApprovalFlowStep of the corresponding ApprovalFlow
        if (! $marketingMediaStockRequest->approval || ! $marketingMediaStockRequest->approval->approvalFlow) {
            return false;
        }

        // Get the approval flow steps for this request's approval flow
        $approvalFlowSteps = $marketingMediaStockRequest->approval->approvalFlow->approvalFlowSteps;

        foreach ($approvalFlowSteps as $step) {
            // Check if the user has the role required for this step and belongs to the right division
            if ($user->hasRole($step->role) &&
                (! $step->division_id || $user->division_id == $step->division_id)) {
                // Check if this step is for the current approval's next step
                if ($marketingMediaStockRequest->approval->current_step == $step->step_number) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine whether the user can resubmit the model after rejected.
     */
    public function resubmit(User $user, MarketingMediaStockRequest $marketingMediaStockRequest): bool
    {
        // Allow users with "Admin Marketing" role to resubmit any request
        if ($user->hasRole('Admin Marketing')) {
            return true;
        }

        // Regular users can resubmit their own rejected requests if they're from a marketing division
        return $user->id === $marketingMediaStockRequest->requester_id &&
            $user->division &&
            stripos($user->division->name, 'Marketing') !== false &&
            $user->division->id === $marketingMediaStockRequest->division_id &&
            $marketingMediaStockRequest->approval &&
            $marketingMediaStockRequest->approval->status === 'rejected';
    }
}
