<?php

namespace App\Filament\Resources\AtkTransferStocks;

use App\Filament\Resources\AtkTransferStocks\Pages\ApprovalAtkTransferStock;
use App\Filament\Resources\AtkTransferStocks\Pages\ListAtkTransferStocks;
use App\Filament\Resources\AtkTransferStocks\Pages\ViewAtkTransferStock;
use App\Filament\Resources\AtkTransferStocks\Schemas\AtkTransferStockForm;
use App\Filament\Resources\AtkTransferStocks\Schemas\AtkTransferStockInfolist;
use App\Filament\Resources\AtkTransferStocks\Tables\AtkTransferStocksTable;
use App\Models\AtkTransferStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AtkTransferStockResource extends Resource
{
    protected static ?string $model = AtkTransferStock::class;

    protected static ?string $navigationLabel = 'Transfer Stok';

    protected static ?string $slug = 'atk/transfer-stocks';

    protected static ?string $modelLabel = 'Transfer Stok';

    protected static ?string $pluralModelLabel = 'Transfer Stok';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Alat Tulis Kantor';

    protected static ?string $recordTitleAttribute = 'transfer_number';

    public static function form(Schema $schema): Schema
    {
        return AtkTransferStockForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkTransferStockInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkTransferStocksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\AtkTransferStocks\RelationManagers\AtkTransferStockItemsRelationManager::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAtkTransferStocks::route('/'),
            'view' => ViewAtkTransferStock::route('/view/{record}'),
            'approval' => ApprovalAtkTransferStock::route('/approval'),
        ];
    }
}