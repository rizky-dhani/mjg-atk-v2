<?php

namespace App\Filament\Resources\AtkFloatingStockTransactionHistories;

use App\Filament\Resources\AtkFloatingStockTransactionHistories\Pages\ManageAtkFloatingStockTransactionHistories;
use App\Filament\Resources\AtkFloatingStockTransactionHistories\Schemas\AtkFloatingStockTransactionHistoryInfolist;
use App\Filament\Resources\AtkFloatingStockTransactionHistories\Tables\AtkFloatingStockTransactionHistoryTable;
use App\Models\AtkFloatingStockTransactionHistory;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AtkFloatingStockTransactionHistoryResource extends Resource
{
    protected static ?string $model = AtkFloatingStockTransactionHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.group.atk');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('filament.navigation.parent_item.floating_stock');
    }

    protected static ?string $navigationLabel = 'Riwayat Transaksi';

    protected static ?string $modelLabel = 'Riwayat Transaksi';

    protected static ?string $pluralModelLabel = 'Riwayat Transaksi';

    protected static ?string $slug = 'atk/floating-stock-trx';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkFloatingStockTransactionHistoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkFloatingStockTransactionHistoryTable::configure($table)
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAtkFloatingStockTransactionHistories::route('/'),
        ];
    }
}
