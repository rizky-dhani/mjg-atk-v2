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
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AtkTransferStockResource extends Resource
{
    protected static ?string $model = AtkTransferStock::class;

    protected static ?string $navigationLabel = 'Transfer Stok ATK';

    protected static ?string $slug = 'atk/transfer-stocks';

    protected static ?string $modelLabel = 'Transfer Stok';

    protected static ?string $pluralModelLabel = 'Transfer Stok';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.group.atk');
    }

    protected static ?string $recordTitleAttribute = 'transfer_number';

    protected static ?int $navigationSort = 3;

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->isActiveWhen(fn () => ! request()->routeIs(static::getRouteBaseName().'.approval') && request()->routeIs(static::getRouteBaseName().'.*'))
                ->sort(static::getNavigationSort())
                ->url(static::getNavigationUrl()),
        ];
    }

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

    public static function getPages(): array
    {
        return [
            'index' => ListAtkTransferStocks::route('/'),
            'view' => ViewAtkTransferStock::route('/view/{record}'),
            'approval' => ApprovalAtkTransferStock::route('/approval'),
        ];
    }
}
