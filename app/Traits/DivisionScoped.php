<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait DivisionScoped
{
    public function scopeForUser(Builder $query, User $user): Builder
    {
        $modelSlug = Str::kebab(class_basename(static::class));

        $divisions = $this->getUserDivisionsForModel($user, $modelSlug);

        // Empty array = no matching permissions = no access
        if (empty($divisions)) {
            return $query->whereRaw('0 = 1');
        }

        // Contains null = at least one global permission = no scoping
        if (in_array(null, $divisions, true)) {
            return $query;
        }

        return $query->whereIn('division_id', array_unique($divisions));
    }

    protected function getUserDivisionsForModel(User $user, string $modelSlug): array
    {
        $divisions = [];

        // Direct permissions on the user
        foreach ($user->permissions as $permission) {
            if ($this->permissionMatchesModel($permission->name, $modelSlug)) {
                $divisions = array_merge($divisions, $permission->divisions ?? []);
            }
        }

        // Permissions via roles
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                if ($this->permissionMatchesModel($permission->name, $modelSlug)) {
                    $divisions = array_merge($divisions, $permission->divisions ?? []);
                }
            }
        }

        return $divisions;
    }

    protected function permissionMatchesModel(string $permissionName, string $modelSlug): bool
    {
        return str_ends_with($permissionName, $modelSlug);
    }
}
