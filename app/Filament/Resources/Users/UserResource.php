<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.group.settings');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('initial')
                    ->label('Initial')
                    ->required()
                    ->maxLength(4),
                Select::make('roles')
                    ->required()
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload(),
                Select::make('divisions')
                    ->required()
                    ->multiple()
                    ->relationship('divisions', 'name')
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->badge()
                    ->separator(',')
                    ->limit(3)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (is_array($state) && count($state) > 3) {
                            return implode(', ', $state);
                        }

                        return null;
                    }),
                TextColumn::make('divisions.initial')
                    ->label('Divisi')
                    ->badge()
                    ->separator(',')
                    ->limit(3),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('User updated'),
                DeleteAction::make()
                    ->successNotificationTitle('User deleted'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Users deleted'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
