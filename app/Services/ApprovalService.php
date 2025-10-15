<?php

namespace App\Services;

use App\Models\User;
use App\Models\Approval;
use App\Models\ApprovalFlow;
use App\Models\AtkStockUsage;
use App\Models\AtkStockRequest;
use App\Models\ApprovalFlowStep;
use App\Models\ApprovalHistory;
use Illuminate\Support\Collection;

class ApprovalService
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
        
        if (!$approvalFlow) {
            return false;
        }

        // Get the approval record for this model, creating it if it doesn't exist
        $approval = $model->approval;
        if (!$approval) {
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
        
        if (!$currentStep) {
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
                
                return !$existingApproval;
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
                
                return !$existingApproval;
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
        
        if (!$approvalFlow) {
            return collect();
        }

        // Get the approval record for this model, creating it if it doesn't exist
        $approval = $model->approval;
        if (!$approval) {
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
                    
                    if (!$existingApproval) {
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
                    
                    if (!$existingApproval) {
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
        // Find the active approval flow for this model type
        $approvalFlow = ApprovalFlow::where('model_type', get_class($stockRequest))
            ->where('is_active', true)
            ->first();
        
        if (!$approvalFlow) {
            return false;
        }

        // Get the approval record for this model, creating it if it doesn't exist
        $approval = $stockRequest->approval;
        if (!$approval) {
            // Create an approval record if one doesn't exist
            $approval = $stockRequest->approval()->create([
                'flow_id' => $approvalFlow->id,
                'current_step' => 1,
                'status' => 'pending',
            ]);
        }

        // Get the current step in the approval flow
        $currentStepNumber = $approval->current_step;
        $currentStep = $approvalFlow->approvalFlowSteps()->where('step_number', $currentStepNumber)->first();
        
        if (!$currentStep) {
            // If there's no step for the current step number, return false
            return false;
        }

        // Check if the current step matches the user's role and division
        $userRoleIds = $user->roles->pluck('id')->toArray();
        
        if (is_null($currentStep->division_id)) {
            // For null division_id steps, check if user's role matches and user's division matches stock request's division
            if (in_array($currentStep->role_id, $userRoleIds) &&
                $stockRequest->division_id !== null &&
                $user->division_id == $stockRequest->division_id) {
                
                // Check if this step hasn't been approved yet by this user
                $existingApproval = $approval->approvalStepApprovals()
                    ->where('step_id', $currentStep->id)
                    ->where('user_id', $user->id)
                    ->first();
                
                return !$existingApproval;
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
                
                return !$existingApproval;
            }
        }
        
        return false;
    }

    /**
     * Get approval steps that match both the stock request division and user's division
     */
    public function getMatchingApprovalStepsForStockRequest(AtkStockRequest $stockRequest, User $user): Collection
    {
        // Find the active approval flow for this model type
        $approvalFlow = ApprovalFlow::where('model_type', get_class($stockRequest))
            ->where('is_active', true)
            ->first();
        
        if (!$approvalFlow) {
            return collect();
        }

        // Get the approval record for this model, creating it if it doesn't exist
        $approval = $stockRequest->approval;
        if (!$approval) {
            // Create an approval record if one doesn't exist
            $approval = $stockRequest->approval()->create([
                'flow_id' => $approvalFlow->id,
                'current_step' => 1,
                'status' => 'pending',
            ]);
        }

        // Get the current step in the approval flow
        $currentStepNumber = $approval->current_step;
        $currentStep = $approvalFlow->approvalFlowSteps()->where('step_number', $currentStepNumber)->first();
        
        $matchingSteps = collect();
        
        if ($currentStep) {
            // Check if the current step matches the user's role and division
            $userRoleIds = $user->roles->pluck('id')->toArray();
            
            if (is_null($currentStep->division_id)) {
                // For null division_id steps, check if user's role matches and user's division matches stock request's division
                if (in_array($currentStep->role_id, $userRoleIds) &&
                    $stockRequest->division_id !== null &&
                    $user->division_id == $stockRequest->division_id) {
                    
                    // Check if this step hasn't been approved yet by this user
                    $existingApproval = $approval->approvalStepApprovals()
                        ->where('step_id', $currentStep->id)
                        ->where('user_id', $user->id)
                        ->first();
                    
                    if (!$existingApproval) {
                        $matchingSteps->push($currentStep);
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
                    
                    if (!$existingApproval) {
                        $matchingSteps->push($currentStep);
                    }
                }
            }
        }
        
        return $matchingSteps;
    }

    /**
     * Check if a specific stock usage can be approved by the logged-in user
     */
    public function canUserApproveStockUsage(AtkStockUsage $stockUsage, User $user): bool
    {
        // Find the active approval flow for this model type
        $approvalFlow = ApprovalFlow::where('model_type', get_class($stockUsage))
            ->where('is_active', true)
            ->first();
        
        if (!$approvalFlow) {
            return false;
        }

        // Get the approval record for this model, creating it if it doesn't exist
        $approval = $stockUsage->approval;
        if (!$approval) {
            // Create an approval record if one doesn't exist
            $approval = $stockUsage->approval()->create([
                'flow_id' => $approvalFlow->id,
                'current_step' => 1,
                'status' => 'pending',
            ]);
        }

        // Get the current step in the approval flow
        $currentStepNumber = $approval->current_step;
        $currentStep = $approvalFlow->approvalFlowSteps()->where('step_number', $currentStepNumber)->first();
        
        if (!$currentStep) {
            // If there's no step for the current step number, return false
            return false;
        }

        // Check if the current step matches the user's role and division
        $userRoleIds = $user->roles->pluck('id')->toArray();
        
        if (is_null($currentStep->division_id)) {
            // For null division_id steps, check if user's role matches and user's division matches stock usage's division
            if (in_array($currentStep->role_id, $userRoleIds) &&
                $stockUsage->division_id !== null &&
                $user->division_id == $stockUsage->division_id) {
                
                // Check if this step hasn't been approved yet by this user
                $existingApproval = $approval->approvalStepApprovals()
                    ->where('step_id', $currentStep->id)
                    ->where('user_id', $user->id)
                    ->first();
                
                return !$existingApproval;
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
                
                return !$existingApproval;
            }
        }
        
        return false;
    }

    /**
     * Get approval steps that match both the stock usage division and user's division
     */
    public function getMatchingApprovalStepsForStockUsage(AtkStockUsage $stockUsage, User $user): Collection
    {
        // Find the active approval flow for this model type
        $approvalFlow = ApprovalFlow::where('model_type', get_class($stockUsage))
            ->where('is_active', true)
            ->first();
        
        if (!$approvalFlow) {
            return collect();
        }

        // Get the approval record for this model, creating it if it doesn't exist
        $approval = $stockUsage->approval;
        if (!$approval) {
            // Create an approval record if one doesn't exist
            $approval = $stockUsage->approval()->create([
                'flow_id' => $approvalFlow->id,
                'current_step' => 1,
                'status' => 'pending',
            ]);
        }

        // Get the current step in the approval flow
        $currentStepNumber = $approval->current_step;
        $currentStep = $approvalFlow->approvalFlowSteps()->where('step_number', $currentStepNumber)->first();
        
        $matchingSteps = collect();
        
        if ($currentStep) {
            // Check if the current step matches the user's role and division
            $userRoleIds = $user->roles->pluck('id')->toArray();
            
            if (is_null($currentStep->division_id)) {
                // For null division_id steps, check if user's role matches and user's division matches stock usage's division
                if (in_array($currentStep->role_id, $userRoleIds) &&
                    $stockUsage->division_id !== null &&
                    $user->division_id == $stockUsage->division_id) {
                    
                    // Check if this step hasn't been approved yet by this user
                    $existingApproval = $approval->approvalStepApprovals()
                        ->where('step_id', $currentStep->id)
                        ->where('user_id', $user->id)
                        ->first();
                    
                    if (!$existingApproval) {
                        $matchingSteps->push($currentStep);
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
                    
                    if (!$existingApproval) {
                        $matchingSteps->push($currentStep);
                    }
                }
            }
        }
        
        return $matchingSteps;
    }

    /**
     * Log an approval action to the approval history
     */
    public function logApprovalAction($model, User $user, string $action, string $documentId = null, string $rejectionReason = null, string $notes = null, int $stepId = null)
    {
        return ApprovalHistory::create([
            'approvable_type' => get_class($model),
            'approvable_id' => $model->id,
            'document_id' => $documentId ?? $this->getDocumentId($model),
            'approval_id' => $model->approval->id ?? null,
            'step_id' => $stepId,
            'user_id' => $user->id,
            'action' => $action,
            'rejection_reason' => $rejectionReason,
            'notes' => $notes,
            'metadata' => [
                'model_class' => get_class($model),
                'model_id' => $model->id,
            ]
        ]);
    }

    /**
     * Get document ID from model (e.g., stock request number)
     */
    private function getDocumentId($model)
    {
        // Try common document ID fields
        if (isset($model->stock_request_number)) {
            return $model->stock_request_number;
        } elseif (isset($model->request_number)) {
            return $model->request_number;
        } elseif (isset($model->stock_usage_number)) {
            return $model->stock_usage_number;
        } elseif (isset($model->usage_number)) {
            return $model->usage_number;
        } elseif (isset($model->id)) {
            // Fallback to model ID with prefix
            $prefix = class_basename($model);
            return $prefix . '-' . $model->id;
        }

        return null;
    }

    /**
     * Get approval history for a specific model
     */
    public function getApprovalHistory($model): Collection
    {
        return ApprovalHistory::where('approvable_type', get_class($model))
            ->where('approvable_id', $model->id)
            ->with(['user', 'step'])
            ->orderBy('performed_at', 'asc')
            ->get();
    }

    /**
     * Get approval history for a specific document ID
     */
    public function getApprovalHistoryByDocumentId(string $documentId): Collection
    {
        return ApprovalHistory::where('document_id', $documentId)
            ->with(['user', 'step', 'approvable'])
            ->orderBy('performed_at', 'asc')
            ->get();
    }

    /**
     * Synchronize approval status between main approval record and approval history
     */
    public function syncApprovalStatus($model): void
    {
        $approval = $model->approval;
        if (!$approval) {
            return;
        }

        // Get the latest approval history record for this model
        $latestHistory = ApprovalHistory::where('approvable_type', get_class($model))
            ->where('approvable_id', $model->id)
            ->orderBy('performed_at', 'desc')
            ->first();

        if ($latestHistory) {
            // Update the approval record based on the latest history if needed
            $statusMap = [
                'approved' => 'approved',
                'rejected' => 'rejected',
                'pending' => 'pending',
            ];

            if (isset($statusMap[$latestHistory->action])) {
                $approval->update([
                    'status' => $statusMap[$latestHistory->action]
                ]);
            }
        }
    }

    /**
     * Get the latest action from approval history for a model
     */
    public function getLatestApprovalAction($model): ?ApprovalHistory
    {
        return ApprovalHistory::where('approvable_type', get_class($model))
            ->where('approvable_id', $model->id)
            ->orderBy('performed_at', 'desc')
            ->first();
    }

    /**
     * Create an approval history record when a new approval is created
     */
    public function logNewApproval($model, User $user, string $documentId = null): void
    {
        $this->logApprovalAction(
            $model,
            $user,
            'submitted', // Initial submission
            $documentId,
            null, // No rejection reason
            'Request submitted for approval',
            null // No specific step for initial submission
        );
    }

    /**
     * Process an approval step for a given approval
     *
     * @param Approval $approval The approval to process
     * @param User $user The user processing the approval
     * @param string $action The action (approve/reject)
     * @param string|null $notes Optional notes
     * @return bool True if the approval is completed, false if there are more steps
     */
    public function processApprovalStep(Approval $approval, User $user, string $action, ?string $notes = null): bool
    {
        $approvable = $approval->approvable;

        // Get eligible approval steps for this user
        $eligibleSteps = $this->getEligibleApprovalSteps($approvable, $user);

        if ($eligibleSteps->isEmpty()) {
            throw new \Exception('No eligible approval steps found for this user.');
        }

        // Process the first eligible step (in case there are multiple)
        $step = $eligibleSteps->first();

        // Determine status based on action
        $status = $action === 'approve' ? 'approved' : 'rejected';

        // Create the approval step record
        \App\Models\ApprovalStepApproval::create([
            'approval_id' => $approval->id,
            'step_id' => $step->id,
            'user_id' => $user->id,
            'status' => $status,
            'approved_at' => now(),
            'notes' => $notes ?? null,
        ]);

        // Log to approval history
        $this->logApprovalAction(
            $approvable,
            $user,
            $status, // 'approved' or 'rejected'
            null, // document_id will be auto-generated
            $action === 'reject' ? ($notes ?? 'No reason provided') : null, // rejection_reason
            $notes ?? ($action === 'approve' ? 'Request approved at step ' . $step->step_number . ': ' . $step->step_name : 'Request rejected'),
            $step->id
        );

        // If rejected, mark the overall approval as rejected
        if ($status === 'rejected') {
            $approval->update([
                'status' => 'rejected',
                'current_step' => $step->step_number,
            ]);

            // Log rejection to history
            $this->logApprovalAction(
                $approvable,
                $user,
                'submitted', // Final submission/rejection
                null, // document_id will be auto-generated
                $notes ?? 'No reason provided', // rejection_reason
                'Request rejected at step ' . $step->step_number . ': ' . $step->step_name,
                null // No specific step for final rejection
            );

            // Synchronize approval status
            $this->syncApprovalStatus($approvable);

            return false; // Approval is not completed due to rejection
        } else {
            // Check if all required steps are now approved
            $allSteps = $approval->approvalFlow->approvalFlowSteps->sortBy('step_number');
            $approvedSteps = $approval->approvalStepApprovals->pluck('step_id');

            $unapprovedSteps = $allSteps->filter(function ($step) use ($approvedSteps) {
                return !$approvedSteps->contains($step->id);
            });

            // If no unapproved steps remain, mark the overall approval as approved
            if ($unapprovedSteps->isEmpty()) {
                $approval->update([
                    'status' => 'approved',
                    'current_step' => $allSteps->last()?->step_number ?? null,
                ]);

                // Log final approval to history
                $this->logApprovalAction(
                    $approvable,
                    $user,
                    'submitted', // Final submission/approval
                    null, // document_id will be auto-generated
                    null, // rejection_reason
                    'Request fully approved',
                    null // No specific step for final approval
                );

                // Synchronize approval status
                $this->syncApprovalStatus($approvable);

                return true; // Approval is completed
            } else {
                // Update to the next step number
                $nextStep = $unapprovedSteps->first();
                $approval->update([
                    'current_step' => $nextStep?->step_number ?? $approval->current_step,
                ]);

                // Log progress to history
                $this->logApprovalAction(
                    $approvable,
                    $user,
                    'pending', // Still pending further approvals
                    null, // document_id will be auto-generated
                    null, // rejection_reason
                    'Request awaiting next approval step: ' . ($nextStep?->step_number ?? 'unknown'),
                    $nextStep?->id
                );

                // Synchronize approval status
                $this->syncApprovalStatus($approvable);

                return false; // Approval is not yet completed
            }
        }
    }
    
    /**
     * Create an approval record for a model
     *
     * @param mixed $model The model to create approval for
     * @param string $modelType The model type
     * @return \App\Models\Approval
     */
    public function createApproval($model, string $modelType): \App\Models\Approval
    {
        // Find the active approval flow for this model type
        $approvalFlow = \App\Models\ApprovalFlow::where('model_type', get_class($model))
            ->where('is_active', true)
            ->first();

        if (!$approvalFlow) {
            throw new \Exception('No active approval flow found for model type: ' . get_class($model));
        }

        // Create an approval record if one doesn't exist
        $approval = $model->approval;
        if (!$approval) {
            $approval = $model->approval()->create([
                'flow_id' => $approvalFlow->id,
                'current_step' => 1,
                'status' => 'pending',
            ]);

            // Log initial submission to history
            $this->logNewApproval($model, auth()->user());
        }

        return $approval;
    }

    /**
     * Cancel an approval
     *
     * @param \App\Models\Approval $approval The approval to cancel
     * @param \App\Models\User $user The user cancelling the approval
     * @return void
     */
    public function cancelApproval(\App\Models\Approval $approval, \App\Models\User $user): void
    {
        $approval->update([
            'status' => 'cancelled'
        ]);

        // Log cancellation to history
        $this->logApprovalAction(
            $approval->approvable,
            $user,
            'cancelled',
            null, // document_id will be auto-generated
            null, // rejection_reason
            'Request cancelled by user',
            null // No specific step for cancellation
        );

        // Synchronize approval status
        $this->syncApprovalStatus($approval->approvable);
    }
}
