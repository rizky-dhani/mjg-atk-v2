<?php

namespace App\Filament\Resources\AtkItemPrices;

use App\Filament\Resources\AtkItemPrices\Pages\ListAtkItemPrices;
use App\Filament\Resources\AtkItemPrices\Pages\ViewAtkItemPrice;
use App\Filament\Resources\AtkItemPrices\Schemas\AtkItemPriceForm;
use App\Filament\Resources\AtkItemPrices\Schemas\AtkItemPriceInfolist;
use App\Filament\Resources\AtkItemPrices\Tables\AtkItemPricesTable;
use App\Models\AtkItemPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AtkItemPriceResource extends Resource
{
    protected static ?string $model = AtkItemPrice::class;

    protected static ?string $navigationLabel = 'Harga Item';

    protected static ?string $slug = 'atk/item-prices';

    protected static ?string $modelLabel = 'Harga Item ATK';

    protected static ?string $pluralModelLabel = 'Harga Item ATK';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.group.atk');
    }

    public static function form(Schema $schema): Schema
    {
        return AtkItemPriceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkItemPriceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkItemPricesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PriceHistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAtkItemPrices::route('/'),
            'view' => ViewAtkItemPrice::route('/view/{record}'),
        ];
    }
}
