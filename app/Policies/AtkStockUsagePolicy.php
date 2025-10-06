<?php

namespace App\Policies;

use App\Models\AtkStockUsage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AtkStockUsagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view stock-usages');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkStockUsage $atkStockUsage): bool
    {
        // Users can view their own usages or if they have the permission
        return $user->id === $atkStockUsage->requester_id || $user->can('view stock-usages');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create stock-usages');
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
        // Check if user has permission to approve stock usages
        if (!$user->can('approve stock-usages')) {
            return false;
        }

        // Check if there's an approval for this usage
        if (!$atkStockUsage->approval) {
            return false;
        }

        // Get the current approval step
        $currentStep = $atkStockUsage->approval->approvalFlow->approvalFlowSteps()
            ->where('step_number', $atkStockUsage->approval->current_step)
            ->first();

        if (!$currentStep) {
            return false;
        }

        // Check if user is authorized for this step
        $approvalService = app(\App\Services\ApprovalService::class);
        return $approvalService->isUserAuthorizedForStep($user, $currentStep, $atkStockUsage->division_id);
    }
}