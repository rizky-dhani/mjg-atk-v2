<?php

namespace App\Filament\Resources\AtkDivisionInventorySettings;

use BackedEnum;
use UnitEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Models\AtkDivisionInventorySetting;
use App\Filament\Resources\AtkDivisionInventorySettings\Pages\EditAtkDivisionInventorySetting;
use App\Filament\Resources\AtkDivisionInventorySettings\Pages\ListAtkDivisionInventorySettings;
use App\Filament\Resources\AtkDivisionInventorySettings\Pages\CreateAtkDivisionInventorySetting;
use App\Filament\Resources\AtkDivisionInventorySettings\Schemas\AtkDivisionInventorySettingForm;
use App\Filament\Resources\AtkDivisionInventorySettings\Tables\AtkDivisionInventorySettingsTable;

class AtkDivisionInventorySettingResource extends Resource
{
    protected static ?string $model = AtkDivisionInventorySetting::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;
    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Stock Limit - ATK';
    protected static ?string $modelLabel = 'Stock Limit';
    protected static ?string $pluralModelLabel = 'Stock Limits';

    public static function form(Schema $schema): Schema
    {
        return AtkDivisionInventorySettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkDivisionInventorySettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAtkDivisionInventorySettings::route('/'),
        ];
    }
}
