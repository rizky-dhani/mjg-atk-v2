<?php

namespace App\Filament\Resources\AtkItemPriceHistories;

use App\Filament\Resources\AtkItemPriceHistories\Pages\ListAtkItemPriceHistories;
use App\Filament\Resources\AtkItemPriceHistories\Pages\ViewAtkItemPriceHistory;
use App\Filament\Resources\AtkItemPriceHistories\Schemas\AtkItemPriceHistoryForm;
use App\Filament\Resources\AtkItemPriceHistories\Schemas\AtkItemPriceHistoryInfolist;
use App\Filament\Resources\AtkItemPriceHistories\Tables\AtkItemPriceHistoriesTable;
use App\Models\AtkItemPriceHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AtkItemPriceHistoryResource extends Resource
{
    protected static ?string $model = AtkItemPriceHistory::class;

    protected static ?string $navigationLabel = 'Histori Harga';

    protected static ?string $slug = 'atk/price-histories';

    protected static ?string $modelLabel = 'Histori Harga Item';

    protected static ?string $pluralModelLabel = 'Histori Harga Item';

    protected static ?string $navigationParentItem = 'Stok Inventaris';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Alat Tulis Kantor';

    public static function form(Schema $schema): Schema
    {
        return AtkItemPriceHistoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkItemPriceHistoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkItemPriceHistoriesTable::configure($table);
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
            'index' => ListAtkItemPriceHistories::route('/'),
            'view' => ViewAtkItemPriceHistory::route('/view/{record}'),
        ];
    }
}