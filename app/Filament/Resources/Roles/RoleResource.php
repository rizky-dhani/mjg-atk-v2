<?php

namespace App\Filament\Resources\Roles;

use UnitEnum;
use BackedEnum;
use App\Models\Role;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ViewRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Schemas\RoleForm;
use App\Filament\Resources\Roles\Tables\RolesTable;
use App\Filament\Resources\Roles\Schemas\RoleInfolist;
use App\Filament\Resources\Roles\RelationManagers\UsersRelationManager;
use App\Filament\Resources\Roles\RelationManagers\PermissionsRelationManager;
use Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource\RelationManager\UserRelationManager;
use Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource\RelationManager\PermissionRelationManager;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;
    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    protected static ?string $recordTitleAttribute = 'name';
    public function viewAny(User $user)
    {
        return $user->hasRole('Super Admin');
    }
    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PermissionsRelationManager::class,
            UsersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'view' => ViewRole::route('/view/{record}')
        ];
    }
}
