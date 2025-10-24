<?php

namespace App\Filament\Resources\MarketingMediaDivisionStockSettings;

use App\Filament\Resources\MarketingMediaDivisionStockSettings\Pages\ListMarketingMediaDivisionStockSettings;
use App\Filament\Resources\MarketingMediaDivisionStockSettings\Schemas\MarketingMediaDivisionStockSettingForm;
use App\Filament\Resources\MarketingMediaDivisionStockSettings\Tables\MarketingMediaDivisionStockSettingsTable;
use App\Models\MarketingMediaDivisionStockSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MarketingMediaDivisionStockSettingResource extends Resource
{
    protected static ?string $model = MarketingMediaDivisionStockSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Stock Limit - Marketing Media';

    protected static ?string $modelLabel = 'Marketing Media Stock Limit';

    protected static ?string $pluralModelLabel = 'Marketing Media Stock Limits';

    public static function form(Schema $schema): Schema
    {
        return MarketingMediaDivisionStockSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketingMediaDivisionStockSettingsTable::configure($table);
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
            'index' => ListMarketingMediaDivisionStockSettings::route('/'),
        ];
    }
}
