<?php

namespace App\Policies;

use App\Models\AtkStockRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AtkStockRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view stock-requests');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkStockRequest $atkStockRequest): bool
    {
        // Users can view their own requests or if they have the permission
        return $user->id === $atkStockRequest->requester_id || $user->can('view stock-requests');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create stock-requests');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AtkStockRequest $atkStockRequest): bool
    {
        // Users can update their own pending requests
        return $user->id === $atkStockRequest->requester_id && 
               $atkStockRequest->approval && 
               $atkStockRequest->approval->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AtkStockRequest $atkStockRequest): bool
    {
        // Users can delete their own pending requests
        return $user->id === $atkStockRequest->requester_id && 
               $atkStockRequest->approval && 
               $atkStockRequest->approval->status === 'pending';
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, AtkStockRequest $atkStockRequest): bool
    {
        // Check if user has permission to approve stock requests
        if (!$user->can('approve stock-requests')) {
            return false;
        }

        // Check if there's an approval for this request
        if (!$atkStockRequest->approval) {
            return false;
        }

        // Get the current approval step
        $currentStep = $atkStockRequest->approval->approvalFlow->approvalFlowSteps()
            ->where('step_number', $atkStockRequest->approval->current_step)
            ->first();

        if (!$currentStep) {
            return false;
        }

        // Check if user is authorized for this step
        $approvalService = app(\App\Services\ApprovalService::class);
        return $approvalService->isUserAuthorizedForStep($user, $currentStep, $atkStockRequest->division_id);
    }
}