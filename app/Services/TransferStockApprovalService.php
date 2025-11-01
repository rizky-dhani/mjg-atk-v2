<?php

namespace App\Services;

use App\Models\AtkTransferStock;
use App\Models\Approval;
use App\Models\ApprovalFlowStep;
use App\Models\ApprovalStepApproval;
use Illuminate\Support\Facades\Auth;

class TransferStockApprovalService
{
    /**
     * Check if the current user can approve the transfer stock request
     */
    public function canApprove(AtkTransferStock $transferStock): bool
    {
        $user = Auth::user();
        $approval = $transferStock->approval;

        if (!$approval || $approval->status !== 'pending') {
            return false;
        }

        // Find the current approval flow step
        $currentStep = $approval->approvalFlow->approvalFlowSteps()
            ->where('step_number', $approval->current_step)
            ->first();

        if (!$currentStep) {
            return false;
        }

        // Check if user has the right role for this step (using Spatie roles permissions)
        $canApprove = false;
        
        $roleName = $currentStep->role ? $currentStep->role->name : null;
        if ($roleName && $user->hasRole($roleName)) {
            if ($currentStep->division_id) {
                $canApprove = $currentStep->division_id == $user->division_id;
            } else {
                $canApprove = true; // No specific division required
            }
        }

        // For steps with null division_id (like Source Division Head), 
        // check if user's division matches any of the item's source divisions
        if (!$canApprove && is_null($currentStep->division_id)) {
            $sourceDivisionIds = $transferStock->transferStockItems->pluck('source_division_id')->toArray();
            $canApprove = in_array($user->division_id, $sourceDivisionIds);
        }

        // Check if this user has already approved this step
        if ($canApprove) {
            $existingApproval = ApprovalStepApproval::where('approval_id', $approval->id)
                ->where('step_id', $currentStep->id)
                ->where('user_id', $user->id)
                ->first();
                
            if ($existingApproval) {
                $canApprove = false;
            }
        }

        return $canApprove;
    }

    /**
     * Approve the transfer stock request
     */
    public function approve(AtkTransferStock $transferStock): bool
    {
        $user = Auth::user();
        $approval = $transferStock->approval;

        if (!$approval || $approval->status !== 'pending') {
            return false;
        }

        // Find the current approval flow step
        $currentStep = $approval->approvalFlow->approvalFlowSteps()
            ->where('step_number', $approval->current_step)
            ->first();

        if (!$currentStep) {
            return false;
        }

        // Process the approval
        $approvalStepApproval = new ApprovalStepApproval();
        $approvalStepApproval->approval_id = $approval->id;
        $approvalStepApproval->step_id = $currentStep->id;
        $approvalStepApproval->user_id = $user->id;
        $approvalStepApproval->approved_at = now();
        $approvalStepApproval->status = 'approved';
        $approvalStepApproval->save();

        // Create approval history
        $approvalHistory = new \App\Models\ApprovalHistory();
        $approvalHistory->approvable_type = get_class($transferStock);
        $approvalHistory->approvable_id = $transferStock->id;
        $approvalHistory->document_id = $transferStock->transfer_number;
        $approvalHistory->approval_id = $approval->id;
        $approvalHistory->step_id = $currentStep->id;
        $approvalHistory->user_id = $user->id;
        $approvalHistory->action = 'approved';
        $approvalHistory->performed_at = now();
        $approvalHistory->save();

        // Check if all required steps are approved
        $flow = $approval->approvalFlow;
        $completedSteps = ApprovalStepApproval::where('approval_id', $approval->id)
            ->where('status', 'approved')
            ->count();
        
        $totalSteps = $flow->approvalFlowSteps()->count();

        if ($completedSteps == $totalSteps) {
            // All steps approved
            $approval->status = 'approved';
            $transferStock->status = 'approved';
        } else {
            // Move to next step
            $approval->current_step = $approval->current_step + 1;
        }
        
        $approval->save();
        $transferStock->save();

        return true;
    }

    /**
     * Reject the transfer stock request
     */
    public function reject(AtkTransferStock $transferStock, string $rejectionReason): bool
    {
        $user = Auth::user();
        $approval = $transferStock->approval;

        if (!$approval || $approval->status !== 'pending') {
            return false;
        }

        // Find the current approval flow step
        $currentStep = $approval->approvalFlow->approvalFlowSteps()
            ->where('step_number', $approval->current_step)
            ->first();

        if (!$currentStep) {
            return false;
        }

        // Process the rejection
        $approvalStepApproval = new ApprovalStepApproval();
        $approvalStepApproval->approval_id = $approval->id;
        $approvalStepApproval->step_id = $currentStep->id;
        $approvalStepApproval->user_id = $user->id;
        $approvalStepApproval->approved_at = now();
        $approvalStepApproval->status = 'rejected';
        $approvalStepApproval->notes = $rejectionReason;
        $approvalStepApproval->save();

        // Create approval history
        $approvalHistory = new \App\Models\ApprovalHistory();
        $approvalHistory->approvable_type = get_class($transferStock);
        $approvalHistory->approvable_id = $transferStock->id;
        $approvalHistory->document_id = $transferStock->transfer_number;
        $approvalHistory->approval_id = $approval->id;
        $approvalHistory->step_id = $currentStep->id;
        $approvalHistory->user_id = $user->id;
        $approvalHistory->action = 'rejected';
        $approvalHistory->rejection_reason = $rejectionReason;
        $approvalHistory->performed_at = now();
        $approvalHistory->save();

        // Update approval and record status
        $approval->status = 'rejected';
        $transferStock->status = 'rejected';
        
        $approval->save();
        $transferStock->save();

        return true;
    }
}