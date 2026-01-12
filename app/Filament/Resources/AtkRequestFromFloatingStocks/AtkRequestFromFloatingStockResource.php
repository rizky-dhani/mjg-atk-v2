<?php

namespace App\Filament\Resources\AtkRequestFromFloatingStocks;

use App\Filament\Resources\AtkRequestFromFloatingStocks\Pages\ListAtkRequestFromFloatingStocks;
use App\Filament\Resources\AtkRequestFromFloatingStocks\Schemas\AtkRequestFromFloatingStockForm;
use App\Filament\Resources\AtkRequestFromFloatingStocks\Tables\AtkRequestFromFloatingStocksTable;
use App\Models\AtkRequestFromFloatingStock;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AtkRequestFromFloatingStockResource extends Resource
{
    protected static ?string $model = AtkRequestFromFloatingStock::class;

    protected static ?string $navigationLabel = 'Minta Stok Umum';

    protected static ?string $slug = 'atk/floating-requests';

    protected static ?string $modelLabel = 'Permintaan Stok Umum';

    protected static ?string $pluralModelLabel = 'Permintaan Stok Umum';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowTopRightOnSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Alat Tulis Kantor';

    protected static ?string $recordTitleAttribute = 'request_number';

    protected static ?int $navigationSort = 5;

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
        return AtkRequestFromFloatingStockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkRequestFromFloatingStocksTable::configure($table);
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
            'index' => ListAtkRequestFromFloatingStocks::route('/'),
            'approval' => \App\Filament\Resources\AtkRequestFromFloatingStocks\Pages\ApprovalAtkRequestFromFloatingStock::route('/approval'),
        ];
    }
}
