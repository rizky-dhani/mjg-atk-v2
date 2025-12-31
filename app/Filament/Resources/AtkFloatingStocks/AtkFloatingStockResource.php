<?php

namespace App\Filament\Resources\AtkFloatingStocks;

use App\Filament\Resources\AtkFloatingStocks\Pages\CreateAtkFloatingStock;
use App\Filament\Resources\AtkFloatingStocks\Pages\EditAtkFloatingStock;
use App\Filament\Resources\AtkFloatingStocks\Pages\ListAtkFloatingStocks;
use App\Filament\Resources\AtkFloatingStocks\RelationManagers\TransactionsRelationManager;
use App\Filament\Resources\AtkFloatingStocks\Schemas\AtkFloatingStockForm;
use App\Filament\Resources\AtkFloatingStocks\Tables\AtkFloatingStocksTable;
use App\Models\AtkFloatingStock;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AtkFloatingStockResource extends Resource
{
    protected static ?string $model = AtkFloatingStock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Alat Tulis Kantor';

    protected static ?string $navigationLabel = 'Floating Stock';

    protected static ?string $modelLabel = 'Floating Stock';

    public static function form(Schema $schema): Schema
    {
        return AtkFloatingStockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkFloatingStocksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAtkFloatingStocks::route('/'),
            'create' => CreateAtkFloatingStock::route('/create'),
            'edit' => EditAtkFloatingStock::route('/{record}/edit'),
        ];
    }
}
