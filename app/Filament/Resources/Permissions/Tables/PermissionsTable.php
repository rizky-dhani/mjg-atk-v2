<?php

namespace App\Filament\Resources\Permissions\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Actions\GenerateModelPermissionsAction;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                GenerateModelPermissionsAction::make(),
            ])
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->successNotificationTitle('Permission updated'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Permissions deleted'),
                ]),
            ]);
    }
}
