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

        // 0. Priority: Specific User
        if ($currentStep->user_id) {
            if ($user->id === $currentStep->user_id) {
                // Check if this step hasn't been approved yet by this user
                $existingApproval = $approval->approvalStepApprovals()
                    ->where('step_id', $currentStep->id)
                    ->where('user_id', $user->id)
                    ->first();

                return ! $existingApproval;
            }

            return false;
        }

        if (is_null($currentStep->division_id)) {
            // For null division_id steps, the logic depends on the model type
            if (get_class($model) === \App\Models\AtkTransferStock::class) {
                // For AtkTransferStock, check based on step name:
                // - "Division Head": should match requesting division
                // - "Source Division Head": should match source division
                $canApprove = false;

                if ($currentStep->step_name == 'Division Head') {
                    // Division Head step - check against requesting division
                    $canApprove = isset($model->requesting_division_id) &&
                                 $model->requesting_division_id !== null &&
                                 $user->belongsToDivision($model->requesting_division_id);
                } elseif ($currentStep->step_name == 'Source Division Head') {
                    // Source Division Head step - check against source division
                    $canApprove = isset($model->source_division_id) &&
                                 $model->source_division_id !== null &&
                                 $user->belongsToDivision($model->source_division_id);
                }

                if ($canApprove && in_array($currentStep->role_id, $userRoleIds)) {
                    // Check if this step hasn't been approved yet by this user
                    $existingApproval = $approval->approvalStepApprovals()
                        ->where('step_id', $currentStep->id)
                        ->where('user_id', $user->id)
                        ->first();

                    return ! $existingApproval;
                }
            } else {
                // For other models, check if user's role matches and user's division matches model's division
                if (in_array($currentStep->role_id, $userRoleIds) &&
                    isset($model->division_id) && $model->division_id !== null &&
                    $user->belongsToDivision($model->division_id)) {

                    // Check if this step hasn't been approved yet by this user
                    $existingApproval = $approval->approvalStepApprovals()
                        ->where('step_id', $currentStep->id)
                        ->where('user_id', $user->id)
                        ->first();

                    return ! $existingApproval;
                }
            }
        } else {
            // For non-null division_id steps, check if both role and division match
            // These users can approve requests from any division
            if (in_array($currentStep->role_id, $userRoleIds) && $user->belongsToDivision($currentStep->division_id)) {

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

            // 0. Priority: Specific User
            if ($currentStep->user_id) {
                if ($user->id === $currentStep->user_id) {
                    // Check if this step hasn't been approved yet by this user
                    $existingApproval = $approval->approvalStepApprovals()
                        ->where('step_id', $currentStep->id)
                        ->where('user_id', $user->id)
                        ->first();

                    if (! $existingApproval) {
                        $eligibleSteps->push($currentStep);
                    }
                }

                return $eligibleSteps;
            }

            if (is_null($currentStep->division_id)) {
                // For null division_id steps, the logic depends on the model type
                if (get_class($model) === \App\Models\AtkTransferStock::class) {
                    // For AtkTransferStock, check based on step name:
                    // - "Division Head": should match requesting division
                    // - "Source Division Head": should match source division
                    $canApprove = false;

                    if ($currentStep->step_name == 'Division Head') {
                        // Division Head step - check against requesting division
                        $canApprove = isset($model->requesting_division_id) &&
                                     $model->requesting_division_id !== null &&
                                     $user->belongsToDivision($model->requesting_division_id);
                    } elseif ($currentStep->step_name == 'Source Division Head') {
                        // Source Division Head step - check against source division
                        $canApprove = isset($model->source_division_id) &&
                                     $model->source_division_id !== null &&
                                     $user->belongsToDivision($model->source_division_id);
                    }

                    if ($canApprove && in_array($currentStep->role_id, $userRoleIds)) {
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
                    // For other models, check if user's role matches and user's division matches model's division
                    if (in_array($currentStep->role_id, $userRoleIds) &&
                        isset($model->division_id) && $model->division_id !== null &&
                        $user->belongsToDivision($model->division_id)) {

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
            } else {
                // For non-null division_id steps, check if both role and division match
                // These users can approve requests from any division
                if (in_array($currentStep->role_id, $userRoleIds) && $user->belongsToDivision($currentStep->division_id)) {

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

    /**
     * Check if a specific AtkTransferStock can be approved by the logged-in user
     * For transfer stocks, approval logic is different as it involves source division
     */
    public function canUserApproveTransferStock(\App\Models\AtkTransferStock $transferStock, User $user): bool
    {
        return $this->canUserApprove($transferStock, $user);
    }

    /**
     * Get eligible approval steps for a user to approve a specific AtkTransferStock
     */
    public function getEligibleApprovalStepsForTransferStock(\App\Models\AtkTransferStock $transferStock, User $user): Collection
    {
        return $this->getEligibleApprovalSteps($transferStock, $user);
    }
}
