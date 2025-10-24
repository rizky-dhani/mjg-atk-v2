<?php

namespace App\Filament\Resources\MarketingMediaDivisionStocks;

use App\Filament\Resources\MarketingMediaDivisionStocks\Pages\ListMarketingMediaDivisionStocks;
use App\Filament\Resources\MarketingMediaDivisionStocks\Pages\ViewMarketingMediaDivisionStock;
use App\Filament\Resources\MarketingMediaDivisionStocks\Schemas\MarketingMediaDivisionStockForm;
use App\Filament\Resources\MarketingMediaDivisionStocks\Schemas\MarketingMediaDivisionStockInfolist;
use App\Filament\Resources\MarketingMediaDivisionStocks\Tables\MarketingMediaDivisionStocksTable;
use App\Models\MarketingMediaDivisionStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MarketingMediaDivisionStockResource extends Resource
{
    protected static ?string $model = MarketingMediaDivisionStock::class;

    protected static ?string $navigationLabel = 'Stok Inventaris';

    protected static ?string $slug = 'marketing-media/inventory-stocks';

    protected static ?string $modelLabel = 'Stok Inventaris Marketing Media';

    protected static ?string $pluralModelLabel = 'Stok Inventaris Marketing Media';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PaperClip;

    protected static string|UnitEnum|null $navigationGroup = 'Marketing Media';

    public static function form(Schema $schema): Schema
    {
        return MarketingMediaDivisionStockForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MarketingMediaDivisionStockInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketingMediaDivisionStocksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\MarketingMediaDivisionStocks\RelationManagers\MarketingMediaStockRequestsRelationManager::class,
            \App\Filament\Resources\MarketingMediaDivisionStocks\RelationManagers\MarketingMediaStockUsagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketingMediaDivisionStocks::route('/'),
            'view' => ViewMarketingMediaDivisionStock::route('/view/{record}'),
        ];
    }
}
