<?php

namespace App\Policies;

use App\Models\MarketingMediaDivisionStockSetting;
use App\Models\User;

class MarketingMediaDivisionStockSettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any marketing-media-division-stock-setting') || $user->hasRole('Admin') && $user->division->initial === 'GA' || $user->hasRole('Admin') && $user->division->initial === 'IPC';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MarketingMediaDivisionStockSetting $marketingMediaDivisionStockSetting): bool
    {
        return $user->can('view marketing-media-division-stock-setting') || $user->hasRole('Admin') && $user->division->initial === 'GA' || $user->hasRole('Admin') && $user->division->initial === 'IPC';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create marketing-media-division-stock-setting') || $user->hasRole('Admin') && $user->division->initial === 'GA' || $user->hasRole('Admin') && $user->division->initial === 'IPC';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MarketingMediaDivisionStockSetting $marketingMediaDivisionStockSetting): bool
    {
        return $user->can('edit marketing-media-division-stock-setting') || $user->hasRole('Admin') && $user->division->initial === 'GA' || $user->hasRole('Admin') && $user->division->initial === 'IPC';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MarketingMediaDivisionStockSetting $marketingMediaDivisionStockSetting): bool
    {
        return $user->can('delete marketing-media-division-stock-setting') || $user->hasRole('Admin') && $user->division->initial === 'GA' || $user->hasRole('Admin') && $user->division->initial === 'IPC';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MarketingMediaDivisionStockSetting $marketingMediaDivisionStockSetting): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MarketingMediaDivisionStockSetting $marketingMediaDivisionStockSetting): bool
    {
        return false;
    }
}
