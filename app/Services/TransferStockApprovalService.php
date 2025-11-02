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
            // All steps approved - process stock transfer
            $approval->status = 'approved';
            $transferStock->status = 'approved';
            
            // Process the stock transfer
            $this->processStockTransfer($transferStock);
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
    
    /**
     * Check if the user is the last approver in the approval flow
     */
    public function isLastApprover(AtkTransferStock $transferStock): bool
    {
        $user = Auth::user();
        $approval = $transferStock->approval;

        if (!$approval) {
            return false;
        }

        // Get the total number of steps in the approval flow
        $totalSteps = $approval->approvalFlow->approvalFlowSteps()->count();

        // Check if the current step is the last step
        $isCurrentStepLast = ($approval->current_step == $totalSteps);

        if (!$isCurrentStepLast) {
            return false;
        }

        // Check if this user can approve the current (last) step
        return $this->canApprove($transferStock);
    }
    
    /**
     * Check if the user is the first approver in the approval flow
     */
    public function isFirstApprover(AtkTransferStock $transferStock): bool
    {
        $user = Auth::user();
        $approval = $transferStock->approval;

        if (!$approval) {
            return false;
        }

        // Check if the current step is the first step
        $isCurrentStepFirst = ($approval->current_step == 1);

        if (!$isCurrentStepFirst) {
            return false;
        }

        // Check if this user can approve the current (first) step
        return $this->canApprove($transferStock);
    }
    
    /**
     * Process the stock transfer when approval is complete
     */
    private function processStockTransfer(AtkTransferStock $transferStock): bool
    {
        // Loop through each transfer stock item and update the division stocks
        foreach ($transferStock->transferStockItems as $transferItem) {
            $quantity = $transferItem->quantity;
            $itemId = $transferItem->item_id;
            $sourceDivisionId = $transferItem->source_division_id;
            $requestingDivisionId = $transferStock->requesting_division_id;
            
            // Reduce stock from source division
            $sourceStock = \App\Models\AtkDivisionStock::where('division_id', $sourceDivisionId)
                ->where('item_id', $itemId)
                ->first();
                
            if ($sourceStock && $sourceStock->current_stock >= $quantity) {
                $sourceStock->current_stock -= $quantity;
                $sourceStock->save();
            } else {
                // If source doesn't have enough stock, we should handle this appropriately
                \Illuminate\Support\Facades\Log::error("Insufficient stock for item {$itemId} in division {$sourceDivisionId}");
                continue;
            }
            
            // Add stock to requesting division
            $requestingStock = \App\Models\AtkDivisionStock::firstOrCreate(
                [
                    'division_id' => $requestingDivisionId,
                    'item_id' => $itemId,
                ],
                [
                    'category_id' => $transferItem->itemCategory ? $transferItem->itemCategory->id : $transferItem->item->category_id,
                    'current_stock' => 0,
                    'max_stock_limit' => 0, // This may need to be set differently
                ]
            );
            
            $requestingStock->current_stock += $quantity;
            $requestingStock->save();
        }
        
        return true;
    }
    
    /**
     * Check if the user is the requester of the transfer stock
     */
    public function isRequester(AtkTransferStock $transferStock): bool
    {
        $user = Auth::user();
        return $user && $user->id == $transferStock->requester_id;
    }
}