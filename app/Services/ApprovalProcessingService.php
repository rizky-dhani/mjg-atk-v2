<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\ApprovalHistory;
use App\Models\AtkDivisionStock;
use App\Models\AtkStockRequest;
use App\Models\AtkStockUsage;
use App\Models\MarketingMediaStockRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class ApprovalProcessingService
{
    protected ApprovalValidationService $validationService;
    protected ApprovalHistoryService $historyService;
    protected StockUpdateService $stockUpdateService;

    public function __construct(
        ApprovalValidationService $validationService,
        ApprovalHistoryService $historyService,
        StockUpdateService $stockUpdateService
    ) {
        $this->validationService = $validationService;
        $this->historyService = $historyService;
        $this->stockUpdateService = $stockUpdateService;
    }

    /**
     * Process an approval step for a given approval
     *
     * @param  Approval  $approval  The approval to process
     * @param  User  $user  The user processing the approval
     * @param  string  $action  The action (approve/reject)
     * @param  string|null  $notes  Optional notes
     * @return bool True if the approval is completed, false if there are more steps
     */
    public function processApprovalStep(Approval $approval, User $user, string $action, ?string $notes = null): bool
    {
        $approvable = $approval->approvable;

        // Get eligible approval steps for this user
        $eligibleSteps = $this->validationService->getEligibleApprovalSteps($approvable, $user);

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
        $this->historyService->logApprovalAction(
            $approvable,
            $user,
            $status, // 'approved' or 'rejected'
            null, // document_id will be auto-generated
            $action === 'reject' ? ($notes ?? 'No reason provided') : null, // rejection_reason
            $notes ?? ($action === 'approve' ? 'Request approved at step '.$step->step_number.': '.$step->step_name : 'Request rejected'),
            $step->id
        );

        // If rejected, mark the overall approval as rejected
        if ($status === 'rejected') {
            $approval->update([
                'status' => 'rejected',
                'current_step' => $step->step_number,
            ]);

            // Synchronize approval status
            $this->syncApprovalStatus($approvable);

            return false; // Approval is not completed due to rejection
        } else {
            // Check if all required steps are now approved
            $allSteps = $approval->approvalFlow->approvalFlowSteps->sortBy('step_number');
            $approvedSteps = $approval->approvalStepApprovals->pluck('step_id');

            $unapprovedSteps = $allSteps->filter(function ($step) use ($approvedSteps) {
                return ! $approvedSteps->contains($step->id);
            });

            // If no unapproved steps remain, mark the overall approval as approved
            if ($unapprovedSteps->isEmpty() && $approval->status !== 'approved') {
                // Use a database transaction to ensure atomicity and prevent race conditions
                \DB::transaction(function () use ($approval, $approvable, $user, $allSteps) {
                    // Re-fetch the approval to ensure we have the latest status
                    $approval->refresh();
                    
                    // Double-check that it's not already approved to prevent race conditions
                    if ($approval->status !== 'approved') {
                        $approval->update([
                            'status' => 'approved',
                            'current_step' => $allSteps->last()?->step_number ?? null,
                        ]);

                        // Log final approval to history
                        $this->historyService->logApprovalAction(
                            $approvable,
                            $user,
                            'submitted', // Final submission/approval
                            null, // document_id will be auto-generated
                            null, // rejection_reason
                            'Request fully approved',
                            null // No specific step for final approval
                        );

                        // If this is a model that requires stock updates when approved, handle it
                        $this->stockUpdateService->handleStockUpdates($approvable);
                    }
                });

                // Synchronize approval status
                $this->syncApprovalStatus($approvable);

                return true; // Approval is completed
            } else {
                // Update to the next step number and set status to 'partially_approved'
                $nextStep = $unapprovedSteps->first();
                $approval->update([
                    'status' => 'partially_approved', // Set status to partially approved since some steps are approved
                    'current_step' => $nextStep?->step_number ?? $allSteps->last()?->step_number,
                ]);

                // Log progress to history
                $this->historyService->logApprovalAction(
                    $approvable,
                    $user,
                    'pending', // Still pending further approvals
                    null, // document_id will be auto-generated
                    null, // rejection_reason
                    'Request awaiting next approval step: '.($nextStep?->step_number ?? 'unknown'),
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
     * @param  mixed  $model  The model to create approval for
     * @param  string  $modelType  The model type
     */
    public function createApproval($model, string $modelType): \App\Models\Approval
    {
        // Find the active approval flow for this model type
        $approvalFlow = \App\Models\ApprovalFlow::where('model_type', get_class($model))
            ->where('is_active', true)
            ->first();

        if (! $approvalFlow) {
            throw new \Exception('No active approval flow found for model type: '.get_class($model));
        }

        // Create an approval record if one doesn't exist
        $approval = $model->approval;
        if (! $approval) {
            $approval = $model->approval()->create([
                'flow_id' => $approvalFlow->id,
                'current_step' => 1,
                'status' => 'pending',
            ]);

            // Log initial submission to history
            $this->historyService->logNewApproval($model, auth()->user());
        }

        return $approval;
    }

    /**
     * Cancel an approval
     *
     * @param  \App\Models\Approval  $approval  The approval to cancel
     * @param  \App\Models\User  $user  The user cancelling the approval
     */
    public function cancelApproval(\App\Models\Approval $approval, \App\Models\User $user): void
    {
        $approval->update([
            'status' => 'cancelled',
        ]);

        // Log cancellation to history
        $this->historyService->logApprovalAction(
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

    /**
     * Resubmit a rejected approval to restart the approval flow from the beginning
     *
     * @param  \App\Models\Approval  $approval  The approval to resubmit
     * @param  \App\Models\User  $user  The user resubmitting the approval
     */
    public function resubmitApproval(Approval $approval, User $user): void
    {
        // Reset the approval to the first step
        $approval->update([
            'status' => 'pending',
            'current_step' => 1,
        ]);

        // Clear all previous step approvals for this approval
        $approval->approvalStepApprovals()->delete();

        // Log the resubmission to history
        $this->historyService->logApprovalAction(
            $approval->approvable,
            $user,
            'submitted', // Action type for resubmission
            null, // document_id will be auto-generated
            null, // rejection_reason
            'Request resubmitted for approval',
            null // No specific step for resubmission
        );
    }

    /**
     * Synchronize approval status between main approval record and approval history
     */
    public function syncApprovalStatus($model): void
    {
        $approval = $model->approval;
        if (! $approval) {
            return;
        }

        // Get the latest approval history record for this model
        $latestHistory = ApprovalHistory::where('approvable_type', get_class($model))
            ->where('approvable_id', $model->id)
            ->orderBy('performed_at', 'desc')
            ->first();

        // Check if approval flow is complete
        $approvalFlow = $approval->approvalFlow;
        $allSteps = $approvalFlow->approvalFlowSteps->sortBy('step_number');
        $approvedSteps = $approval->approvalStepApprovals->pluck('step_id');
        $unapprovedSteps = $allSteps->filter(function ($step) use ($approvedSteps) {
            return ! $approvedSteps->contains($step->id);
        });

        // If all steps are approved, confirm status is 'approved'
        if ($unapprovedSteps->isEmpty()) {
            $approval->update([
                'status' => 'approved',
            ]);
        } 
        // Otherwise, if there are still unapproved steps but some are approved, status should be 'partially_approved'
        // If no steps are approved yet, status should remain 'pending'
        else {
            // Check if any steps have been approved
            $hasApprovedSteps = $approval->approvalStepApprovals()
                ->where('status', 'approved')
                ->exists();
                
            if ($hasApprovedSteps) {
                $approval->update([
                    'status' => 'partially_approved',
                ]);
            } else {
                $approval->update([
                    'status' => 'pending',
                ]);
            }
        }
    }
}