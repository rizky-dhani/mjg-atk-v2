<?php

namespace App\Filament\Resources\UserDivisions;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\UserDivisions\Pages\ManageUserDivisions;
use App\Models\UserDivision;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserDivisionResource extends Resource
{
    protected static ?string $model = UserDivision::class;
    protected static ?string $navigationLabel = 'Divisions';
    protected static ?string $navigationParentItem = 'Users';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;
    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('name')
                ->label('Name')
                ->required(),
                TextInput::make('initial')
                ->label('Initial')
                ->required(),
                Textarea::make('description')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('initial'),
                
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('User Division updated'),
                DeleteAction::make()
                    ->successNotificationTitle('User Division deleted'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('User Divisions deleted'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUserDivisions::route('/'),
        ];
    }
}
