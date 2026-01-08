<?php

namespace App\Filament\Resources\AtkStockTransactions;

use App\Filament\Resources\AtkStockTransactions\Pages\ListAtkStockTransactions;
use App\Filament\Resources\AtkStockTransactions\Pages\ViewAtkStockTransaction;
use App\Filament\Resources\AtkStockTransactions\Schemas\AtkStockTransactionForm;
use App\Filament\Resources\AtkStockTransactions\Schemas\AtkStockTransactionInfolist;
use App\Filament\Resources\AtkStockTransactions\Tables\AtkStockTransactionsTable;
use App\Filament\Resources\AtkTransferStocks\Pages\ApprovalAtkTransferStock;
use App\Models\AtkStockTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AtkStockTransactionResource extends Resource
{
    protected static ?string $model = AtkStockTransaction::class;

    protected static ?string $navigationLabel = 'Riwayat Transfer';

    protected static ?string $slug = 'atk/stock-transactions';

    protected static ?string $modelLabel = 'Riwayat Transfer';

    protected static ?string $pluralModelLabel = 'Riwayat Transfer';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Alat Tulis Kantor';

    protected static ?string $navigationParentItem = 'Transfer Stok ATK';

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
            'approval' => ApprovalAtkTransferStock::route('/approval'),
        ];
    }
}
