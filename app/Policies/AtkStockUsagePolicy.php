<?php

namespace App\Policies;

use App\Models\AtkStockUsage;
use App\Models\User;

class AtkStockUsagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any atk-stock-usage');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkStockUsage $atkStockUsage): bool
    {
        // Users can only view their own usages (where requester_id matches logged in user id)
        return $user->can('view atk-stock-usage');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create atk-stock-usage');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AtkStockUsage $atkStockUsage): bool
    {
        // Users can update their own pending usages
        return $user->id === $atkStockUsage->requester_id &&
            $atkStockUsage->approval &&
            $atkStockUsage->approval->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AtkStockUsage $atkStockUsage): bool
    {
        // Users can delete their own pending usages
        return $user->id === $atkStockUsage->requester_id &&
            $atkStockUsage->approval &&
            $atkStockUsage->approval->status === 'pending';
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, AtkStockUsage $atkStockUsage): bool
    {
        // Use the ApprovalService to check if user can approve this specific usage
        $approvalService = app(\App\Services\ApprovalService::class);

        return $approvalService->canUserApproveStockUsage(
            $atkStockUsage,
            $user,
        );
    }

    /**
     * Determine whether the user can resubmit the model after rejected.
     */
    public function resubmit(User $user, AtkStockUsage $atkStockUsage): bool
    {
        // Users can resubmit their own rejected usages
        return $user->id === $atkStockUsage->requester_id &&
            $atkStockUsage->approval &&
            $atkStockUsage->approval->status === 'rejected';
    }
}
