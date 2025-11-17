<?php

namespace App\Services;

use App\Models\AtkTransferStock;
use App\Models\Approval;
use App\Models\ApprovalFlowStep;
use App\Models\ApprovalStepApproval;
use Illuminate\Support\Facades\Auth;
use App\Services\ApprovalValidationService;
use App\Services\ApprovalHistoryService;
use App\Services\ApprovalProcessingService;
use App\Services\StockUpdateService;

class TransferStockApprovalService
{
    /**
     * Check if the current user can approve the transfer stock request
     */
    public function canApprove(AtkTransferStock $transferStock): bool
    {
        $user = Auth::user();
        $approval = $transferStock->approval;

        if (!$approval || ($approval->status !== 'pending' && $approval->status !== 'partially_approved')) {
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
        $userHasRole = $roleName && $user->hasRole($roleName);

        if ($currentStep->division_id) {
            // For steps with a specific division, check both role and division
            $canApprove = $userHasRole && $currentStep->division_id == $user->division_id;
        } elseif (is_null($currentStep->division_id) && $userHasRole) {
            // For steps with null division_id, check based on the step name:
            // - "Division Head": should match requesting division
            // - "Source Division Head": should match source division
            // - For other step names with null division_id: this should not happen for AtkTransferStock based on the seeder data
            if ($currentStep->step_name == 'Division Head') {
                // Division Head step - check against requesting division
                $canApprove = $user->division_id == $transferStock->requesting_division_id;
            } elseif ($currentStep->step_name == 'Source Division Head') {
                // Source Division Head step - check against source division
                $canApprove = $user->division_id == $transferStock->source_division_id;
            } else {
                // For other step names with null division_id, if user has the required role
                // Default to checking against requesting division (as per your requirement)
                // However, for AtkTransferStock, there shouldn't be other step names besides the two above
                // based on the ApprovalFlowSeeder, so this case shouldn't occur in normal flow
                $canApprove = $user->division_id == $transferStock->requesting_division_id;
            }
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

        if (!$approval) {
            return false;
        }

        // Use the general ApprovalProcessingService to handle the approval
        $validationService = new ApprovalValidationService();
        $historyService = new ApprovalHistoryService();
        $stockUpdateService = app(StockUpdateService::class);
        $processingService = new ApprovalProcessingService($validationService, $historyService, $stockUpdateService);
        $approvalService = new ApprovalService($validationService, $processingService, $historyService, $stockUpdateService);

        // Process the approval step using the service method
        $approvalService->processApprovalStep($approval, $user, 'approve', 'Request approved via table action');

        // Synchronize approval status
        $approvalService->syncApprovalStatus($transferStock);

        return true;
    }

    /**
     * Reject the transfer stock request
     */
    public function reject(AtkTransferStock $transferStock, string $rejectionReason): bool
    {
        $user = Auth::user();
        $approval = $transferStock->approval;

        if (!$approval) {
            return false;
        }

        // Use the general ApprovalProcessingService to handle the rejection
        $validationService = new ApprovalValidationService();
        $historyService = new ApprovalHistoryService();
        $stockUpdateService = app(StockUpdateService::class);
        $processingService = new ApprovalProcessingService($validationService, $historyService, $stockUpdateService);
        $approvalService = new ApprovalService($validationService, $processingService, $historyService, $stockUpdateService);

        // Process the rejection step using the service method
        $approvalService->processApprovalStep($approval, $user, 'reject', $rejectionReason);

        // Synchronize approval status
        $approvalService->syncApprovalStatus($transferStock);

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
     * Check if the user is the requester of the transfer stock
     */
    public function isRequester(AtkTransferStock $transferStock): bool
    {
        $user = Auth::user();
        return $user && $user->id == $transferStock->requester_id;
    }
}