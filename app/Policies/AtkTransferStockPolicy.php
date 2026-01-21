<?php

namespace App\Policies;

use App\Models\AtkTransferStock;
use App\Models\User;
use App\Services\ApprovalService;

class AtkTransferStockPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any atk-transfer-stock');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkTransferStock $atkTransferStock): bool
    {
        return $user->can('view atk-transfer-stock');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create atk-transfer-stock');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AtkTransferStock $atkTransferStock): bool
    {
        // Users can update their own pending requests
        return $user->id === $atkTransferStock->requester_id &&
            $atkTransferStock->approval &&
            $atkTransferStock->approval->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AtkTransferStock $atkTransferStock): bool
    {
        // Users can delete their own pending requests
        return $user->id === $atkTransferStock->requester_id &&
            $atkTransferStock->approval &&
            $atkTransferStock->approval->status === 'pending';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AtkTransferStock $atkTransferStock): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AtkTransferStock $atkTransferStock): bool
    {
        return false;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, AtkTransferStock $atkTransferStock): bool
    {
        // Use the ApprovalService to check if user can approve this specific request
        $approvalService = app(ApprovalService::class);

        return $approvalService->canUserApproveTransferStock(
            $atkTransferStock,
            $user,
        );
    }
}
