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
        return $user->can('view atk-stock-request');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkStockRequest $atkStockRequest): bool
    {
        // Users can view their own requests or if they have the permission
        return $user->id === $atkStockRequest->requester_id || $user->can('view atk-stock-request');
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
        return $approvalService->canUserApproveStockRequest($atkStockRequest, $user);
    }
}