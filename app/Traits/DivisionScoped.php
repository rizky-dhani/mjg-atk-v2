<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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
        $table = config('permission.table_names.role_has_permissions', 'role_has_permissions');
        $modelTable = config('permission.table_names.model_has_permissions', 'model_has_permissions');

        // Direct permissions on the user (model_has_permissions)
        $directDivisions = DB::table($modelTable)
            ->join('permissions', 'permissions.id', '=', "{$modelTable}.permission_id")
            ->where("{$modelTable}.model_id", $user->id)
            ->where("{$modelTable}.model_type", get_class($user))
            ->where('permissions.name', 'like', "%{$modelSlug}")
            ->pluck("{$modelTable}.division_id")
            ->toArray();

        $divisions = array_merge($divisions, $directDivisions);

        // Permissions via roles (role_has_permissions)
        $roleIds = $user->roles->pluck('id')->toArray();

        if (! empty($roleIds)) {
            $roleDivisions = DB::table($table)
                ->join('permissions', 'permissions.id', '=', "{$table}.permission_id")
                ->whereIn("{$table}.role_id", $roleIds)
                ->where('permissions.name', 'like', "%{$modelSlug}")
                ->pluck("{$table}.division_id")
                ->toArray();

            $divisions = array_merge($divisions, $roleDivisions);
        }

        return $divisions;
    }

    protected function permissionMatchesModel(string $permissionName, string $modelSlug): bool
    {
        return str_ends_with($permissionName, $modelSlug);
    }
}
