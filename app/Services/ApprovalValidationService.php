<?php

namespace App\Services;

use App\Models\ApprovalFlow;
use App\Models\AtkStockRequest;
use App\Models\AtkStockUsage;
use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMediaStockUsage;
use App\Models\User;
use Illuminate\Support\Collection;

class ApprovalValidationService
{
    /**
     * Check if a user can approve a specific model (e.g., AtkStockRequest)
     */
    public function canUserApprove($model, User $user): bool
    {
        // Find the active approval flow for this model type
        $approvalFlow = ApprovalFlow::where('model_type', get_class($model))
            ->where('is_active', true)
            ->first();

        if (! $approvalFlow) {
            return false;
        }

        // Get the approval record for this model, creating it if it doesn't exist
        $approval = $model->approval;
        if (! $approval) {
            // Create an approval record if one doesn't exist
            $approval = $model->approval()->create([
                'flow_id' => $approvalFlow->id,
                'current_step' => 1,
                'status' => 'pending',
            ]);
        }

        // Get the current step in the approval flow
        $currentStepNumber = $approval->current_step;
        $currentStep = $approvalFlow->approvalFlowSteps()->where('step_number', $currentStepNumber)->first();

        if (! $currentStep) {
            // If there's no step for the current step number, return false
            return false;
        }

        // Check if the current step matches the user's role and division
        $userRoleIds = $user->roles->pluck('id')->toArray();

        if (is_null($currentStep->division_id)) {
            // For null division_id steps, check if user's role matches and user's division matches model's division
            if (in_array($currentStep->role_id, $userRoleIds) &&
                isset($model->division_id) && $model->division_id !== null &&
                $user->division_id == $model->division_id) {

                // Check if this step hasn't been approved yet by this user
                $existingApproval = $approval->approvalStepApprovals()
                    ->where('step_id', $currentStep->id)
                    ->where('user_id', $user->id)
                    ->first();

                return ! $existingApproval;
            }
        } else {
            // For non-null division_id steps, check if both role and division match
            // These users can approve requests from any division
            if (in_array($currentStep->role_id, $userRoleIds) && $currentStep->division_id == $user->division_id) {

                // Check if this step hasn't been approved yet by this user
                $existingApproval = $approval->approvalStepApprovals()
                    ->where('step_id', $currentStep->id)
                    ->where('user_id', $user->id)
                    ->first();

                return ! $existingApproval;
            }
        }

        return false;
    }

    /**
     * Get eligible approval steps for a user to approve a specific model
     */
    public function getEligibleApprovalSteps($model, User $user): Collection
    {
        // Find the active approval flow for this model type
        $approvalFlow = ApprovalFlow::where('model_type', get_class($model))
            ->where('is_active', true)
            ->first();

        if (! $approvalFlow) {
            return collect();
        }

        // Get the approval record for this model, creating it if it doesn't exist
        $approval = $model->approval;
        if (! $approval) {
            // Create an approval record if one doesn't exist
            $approval = $model->approval()->create([
                'flow_id' => $approvalFlow->id,
                'current_step' => 1,
                'status' => 'pending',
            ]);
        }

        // Get the current step in the approval flow
        $currentStepNumber = $approval->current_step;
        $currentStep = $approvalFlow->approvalFlowSteps()->where('step_number', $currentStepNumber)->first();

        $eligibleSteps = collect();

        if ($currentStep) {
            // Check if the current step matches the user's role and division
            $userRoleIds = $user->roles->pluck('id')->toArray();

            if (is_null($currentStep->division_id)) {
                // For null division_id steps, check if user's role matches and user's division matches model's division
                if (in_array($currentStep->role_id, $userRoleIds) &&
                    isset($model->division_id) && $model->division_id !== null &&
                    $user->division_id == $model->division_id) {

                    // Check if this step hasn't been approved yet by this user
                    $existingApproval = $approval->approvalStepApprovals()
                        ->where('step_id', $currentStep->id)
                        ->where('user_id', $user->id)
                        ->first();

                    if (! $existingApproval) {
                        $eligibleSteps->push($currentStep);
                    }
                }
            } else {
                // For non-null division_id steps, check if both role and division match
                // These users can approve requests from any division
                if (in_array($currentStep->role_id, $userRoleIds) && $currentStep->division_id == $user->division_id) {

                    // Check if this step hasn't been approved yet by this user
                    $existingApproval = $approval->approvalStepApprovals()
                        ->where('step_id', $currentStep->id)
                        ->where('user_id', $user->id)
                        ->first();

                    if (! $existingApproval) {
                        $eligibleSteps->push($currentStep);
                    }
                }
            }
        }

        return $eligibleSteps;
    }

    /**
     * Check if a specific stock request can be approved by the logged-in user
     */
    public function canUserApproveStockRequest(AtkStockRequest $stockRequest, User $user): bool
    {
        return $this->canUserApprove($stockRequest, $user);
    }

    /**
     * Check if a specific marketing media stock request can be approved by the logged-in user
     */
    public function canUserApproveMarketingMediaStockRequest(MarketingMediaStockRequest $marketingMediaStockRequest, User $user): bool
    {
        return $this->canUserApprove($marketingMediaStockRequest, $user);
    }

    /**
     * Check if a specific stock usage can be approved by the logged-in user
     */
    public function canUserApproveStockUsage(AtkStockUsage $stockUsage, User $user): bool
    {
        return $this->canUserApprove($stockUsage, $user);
    }

    /**
     * Check if a specific marketing media stock usage can be approved by the logged-in user
     */
    public function canUserApproveMarketingMediaStockUsage(MarketingMediaStockUsage $marketingMediaStockUsage, User $user): bool
    {
        return $this->canUserApprove($marketingMediaStockUsage, $user);
    }

    /**
     * Get approval steps that match both the stock request division and user's division
     */
    public function getMatchingApprovalStepsForStockRequest(AtkStockRequest $stockRequest, User $user): Collection
    {
        return $this->getEligibleApprovalSteps($stockRequest, $user);
    }

    /**
     * Get approval steps that match both the marketing media stock request division and user's division
     */
    public function getMatchingApprovalStepsForMarketingMediaStockRequest(MarketingMediaStockRequest $marketingMediaStockRequest, User $user): Collection
    {
        return $this->getEligibleApprovalSteps($marketingMediaStockRequest, $user);
    }

    /**
     * Get approval steps that match both the stock usage division and user's division
     */
    public function getMatchingApprovalStepsForStockUsage(AtkStockUsage $stockUsage, User $user): Collection
    {
        return $this->getEligibleApprovalSteps($stockUsage, $user);
    }
}