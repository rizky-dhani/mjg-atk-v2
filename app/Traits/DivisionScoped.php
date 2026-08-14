<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait DivisionScoped
{
    /**
     * Scope the query to records the user is allowed to see.
     *
     * Rule:
     * - Super Admin, GA (General Affairs) and HCG (Human Capital General) see all divisions.
     * - Everyone else sees only their own divisions (from the division_user pivot).
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        // Global scope: Super Admin, GA, HCG see every division's records.
        if ($user->isSuperAdmin() || $user->isGA() || $user->hasDivisionInitial('HCG')) {
            return $query;
        }

        $divisionIds = $user->divisions()->pluck('user_divisions.id');

        // No divisions assigned → no access.
        if ($divisionIds->isEmpty()) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('division_id', $divisionIds);
    }
}
