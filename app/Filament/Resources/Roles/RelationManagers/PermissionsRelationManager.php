<?php

namespace App\Filament\Resources\Roles\RelationManagers;

use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\Actions\GenerateModelPermissionsAction;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';
    public function isReadOnly(): bool
    {
        return false;
    }
    protected static ?string $relatedResource = PermissionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
                GenerateModelPermissionsAction::make(),
            ]);
    }
}
