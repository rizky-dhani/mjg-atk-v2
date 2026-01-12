<?php

namespace App\Policies;

use App\Models\AtkStockRequest;
use App\Models\User;

class AtkStockRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any atk-stock-request');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkStockRequest $atkStockRequest): bool
    {
        // Users can only view their own requests (where requester_id matches logged in user id)
        return $user->can('view atk-stock-request');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create atk-stock-request');
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
        // Use the ApprovalService to check if user can approve this specific request
        $approvalService = app(\App\Services\ApprovalService::class);

        return $approvalService->canUserApproveStockRequest(
            $atkStockRequest,
            $user,
        );
    }

    /**
     * Determine whether the user can resubmit the model after rejected.
     */
    public function resubmit(User $user, AtkStockRequest $atkStockRequest): bool
    {
        // Users can delete their own pending requests
        return $user->id === $atkStockRequest->requester_id &&
            $atkStockRequest->approval &&
            $atkStockRequest->approval->status === 'rejected';
    }
}
