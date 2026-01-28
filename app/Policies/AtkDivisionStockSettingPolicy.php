<?php

namespace App\Policies;

use App\Models\AtkDivisionStockSetting;
use App\Models\User;

class AtkDivisionStockSettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any atk-division-stock-setting') || ($user->hasRole('Admin') && ($user->isGA() || $user->hasDivisionInitial('IPC')));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AtkDivisionStockSetting $atkDivisionStockSetting): bool
    {
        return $user->can('view atk-division-stock-setting') || ($user->hasRole('Admin') && ($user->isGA() || $user->hasDivisionInitial('IPC')));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create atk-division-stock-setting') || ($user->hasRole('Admin') && ($user->isGA() || $user->hasDivisionInitial('IPC')));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AtkDivisionStockSetting $atkDivisionStockSetting): bool
    {
        return $user->can('edit atk-division-stock-setting') || ($user->hasRole('Admin') && ($user->isGA() || $user->hasDivisionInitial('IPC')));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AtkDivisionStockSetting $atkDivisionStockSetting): bool
    {
        return $user->can('delete atk-division-stock-setting') || ($user->hasRole('Admin') && ($user->isGA() || $user->hasDivisionInitial('IPC')));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AtkDivisionStockSetting $atkDivisionStockSetting): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AtkDivisionStockSetting $atkDivisionStockSetting): bool
    {
        return false;
    }
}
