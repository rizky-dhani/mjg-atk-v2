<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        Select::make('guard_name')
                            ->required()
                            ->options([
                                'web' => 'web',
                                'api' => 'api',
                            ])
                            ->label('Guard Name'),
                        Select::make('permissions')
                            ->multiple()
                            ->relationship('permissions', 'name')
                            ->preload()
                            ->label('Permissions')
                            ->helperText('Select permissions to assign to this role')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
