<?php

namespace App\Filament\Resources\AtkDivisionStockSettings;

use App\Filament\Resources\AtkDivisionStockSettings\Pages\ListAtkDivisionStockSettings;
use App\Filament\Resources\AtkDivisionStockSettings\Schemas\AtkDivisionStockSettingForm;
use App\Filament\Resources\AtkDivisionStockSettings\Tables\AtkDivisionStockSettingsTable;
use App\Models\AtkDivisionStockSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AtkDivisionStockSettingResource extends Resource
{
    protected static ?string $model = AtkDivisionStockSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Stock Limit - ATK';

    protected static ?string $modelLabel = 'Stock Limit';

    protected static ?string $pluralModelLabel = 'Stock Limits';

    public static function form(Schema $schema): Schema
    {
        return AtkDivisionStockSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkDivisionStockSettingsTable::configure($table);
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
            'index' => ListAtkDivisionStockSettings::route('/'),
        ];
    }
}
