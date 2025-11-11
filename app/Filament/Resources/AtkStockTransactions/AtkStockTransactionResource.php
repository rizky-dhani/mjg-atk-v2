<?php

namespace App\Filament\Resources\AtkStockTransactions;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\AtkStockTransaction;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\AtkStockTransactions\Pages\ViewAtkStockTransaction;
use App\Filament\Resources\AtkStockTransactions\Pages\ListAtkStockTransactions;
use App\Filament\Resources\AtkStockTransactions\Schemas\AtkStockTransactionForm;
use App\Filament\Resources\AtkStockTransactions\Tables\AtkStockTransactionsTable;
use App\Filament\Resources\AtkStockTransactions\Schemas\AtkStockTransactionInfolist;

class AtkStockTransactionResource extends Resource
{
    protected static ?string $model = AtkStockTransaction::class;

    protected static ?string $navigationLabel = 'Transaksi Stok';

    protected static ?string $slug = 'atk/stock-transactions';

    protected static ?string $modelLabel = 'Transaksi Stok';

    protected static ?string $pluralModelLabel = 'Transaksi Stok';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Alat Tulis Kantor';

    public static function form(Schema $schema): Schema
    {
        return AtkStockTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkStockTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkStockTransactionsTable::configure($table);
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
            'index' => ListAtkStockTransactions::route('/'),
            'view' => ViewAtkStockTransaction::route('/view/{record}'),
        ];
    }
}