<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowStep;
use App\Models\ApprovalStepApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    /**
     * Create a new approval for a model
     *
     * @param Model $model The model that requires approval
     * @param string $flowModelType The model type for the approval flow
     * @return Approval
     */
    public function createApproval(Model $model, string $flowModelType): Approval
    {
        // Find the approval flow for this model type
        $flow = ApprovalFlow::where('model_type', $flowModelType)
            ->where('is_active', true)
            ->first();

        if (!$flow) {
            throw new \Exception("No active approval flow found for {$flowModelType}");
        }

        // Create the approval record
        $approval = new Approval([
            'approvable_type' => get_class($model),
            'approvable_id' => $model->id,
            'flow_id' => $flow->id,
            'current_step' => 1,
            'status' => 'pending'
        ]);
        $approval->save();

        return $approval;
    }

    /**
     * Process an approval step
     *
     * @param Approval $approval The approval to process
     * @param User $user The user processing the approval
     * @param string $action The action (approve/reject)
     * @param string|null $notes Optional notes
     * @return bool True if the approval is completed, false if there are more steps
     */
    public function processApprovalStep(Approval $approval, User $user, string $action, ?string $notes = null): bool
    {
        // Get the current step
        $currentStep = $approval->approvalFlow->approvalFlowSteps()
            ->where('step_number', $approval->current_step)
            ->first();

        if (!$currentStep) {
            throw new \Exception("Approval step not found");
        }

        // Check if user is authorized to approve this step
        if (!$this->isUserAuthorizedForStep($user, $currentStep, $approval->approvable->division_id)) {
            throw new \Exception("User is not authorized to approve this step");
        }

        // Record the approval action
        $stepApproval = new ApprovalStepApproval([
            'approval_id' => $approval->id,
            'step_id' => $currentStep->id,
            'user_id' => $user->id,
            'status' => $action,
            'notes' => $notes
        ]);
        $stepApproval->save();

        if ($action === 'approve') {
            // Check if this is the final step
            $totalSteps = $approval->approvalFlow->approvalFlowSteps()->count();
            
            if ($approval->current_step >= $totalSteps) {
                // Final step approved - complete the approval
                $approval->status = 'approved';
                $approval->save();
                
                return true;
            } else {
                // Move to next step
                $approval->current_step++;
                $approval->save();
                
                return false;
            }
        } else {
            // Rejected - mark approval as rejected
            $approval->status = 'rejected';
            $approval->save();
            
            return true;
        }
    }

    /**
     * Check if a user is authorized to approve a specific step
     *
     * @param User $user The user to check
     * @param ApprovalFlowStep $step The approval step
     * @param int $divisionId The division ID
     * @return bool
     */
    public function isUserAuthorizedForStep(User $user, ApprovalFlowStep $step, int $divisionId): bool
    {
        // Check if user has the required role
        if (!$user->hasRole($step->role->name)) {
            return false;
        }

        // If the step is tied to a specific division, check that too
        if ($step->division_id && $step->division_id != $divisionId) {
            return false;
        }

        return true;
    }

    /**
     * Get pending approvals for a user
     *
     * @param User $user The user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingApprovalsForUser(User $user)
    {
        // Get all approval flows
        $flows = ApprovalFlow::where('is_active', true)->get();
        
        $approvals = collect();
        
        foreach ($flows as $flow) {
            // Get steps for this flow that the user is authorized for
            $steps = $flow->approvalFlowSteps()
                ->whereHas('role', function ($query) use ($user) {
                    $query->whereHas('users', function ($subQuery) use ($user) {
                        $subQuery->where('users.id', $user->id);
                    });
                })
                ->get();
                
            foreach ($steps as $step) {
                // Get approvals at this step
                $stepApprovals = Approval::where('flow_id', $flow->id)
                    ->where('current_step', $step->step_number)
                    ->where('status', 'pending')
                    ->get();
                    
                $approvals = $approvals->merge($stepApprovals);
            }
        }
        
        return $approvals;
    }

    /**
     * Cancel an approval
     *
     * @param Approval $approval The approval to cancel
     * @param User $user The user cancelling the approval
     * @return void
     */
    public function cancelApproval(Approval $approval, User $user): void
    {
        // Check if user is authorized to cancel (typically the requester)
        if ($approval->approvable->user_id != $user->id && !$user->hasRole('Super Admin')) {
            throw new \Exception("User is not authorized to cancel this approval");
        }

        $approval->status = 'cancelled';
        $approval->save();
    }
}