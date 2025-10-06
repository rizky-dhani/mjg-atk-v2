<?php

namespace App\Filament\Resources\AtkDivisionStocks;

use App\Filament\Resources\AtkDivisionStocks\Pages\CreateAtkDivisionStock;
use App\Filament\Resources\AtkDivisionStocks\Pages\EditAtkDivisionStock;
use App\Filament\Resources\AtkDivisionStocks\Pages\ListAtkDivisionStocks;
use App\Filament\Resources\AtkDivisionStocks\Pages\ViewAtkDivisionStock;
use App\Filament\Resources\AtkDivisionStocks\Schemas\AtkDivisionStockForm;
use App\Filament\Resources\AtkDivisionStocks\Schemas\AtkDivisionStockInfolist;
use App\Filament\Resources\AtkDivisionStocks\Tables\AtkDivisionStocksTable;
use App\Models\AtkDivisionStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AtkDivisionStockResource extends Resource
{
    protected static ?string $model = AtkDivisionStock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AtkDivisionStockForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkDivisionStockInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkDivisionStocksTable::configure($table);
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
            'index' => ListAtkDivisionStocks::route('/'),
            'create' => CreateAtkDivisionStock::route('/create'),
            'view' => ViewAtkDivisionStock::route('/{record}'),
            'edit' => EditAtkDivisionStock::route('/{record}/edit'),
        ];
    }
}
