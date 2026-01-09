<?php

namespace App\Filament\Resources\AtkStockUsages;

use App\Filament\Resources\AtkStockUsages\Pages\ApprovalAtkStockUsage;
use App\Filament\Resources\AtkStockUsages\Pages\ListAtkStockUsages;
use App\Filament\Resources\AtkStockUsages\Pages\ViewAtkStockUsage;
use App\Filament\Resources\AtkStockUsages\Schemas\AtkStockUsageForm;
use App\Filament\Resources\AtkStockUsages\Schemas\AtkStockUsageInfolist;
use App\Filament\Resources\AtkStockUsages\Tables\AtkStockUsagesTable;
use App\Models\AtkStockUsage;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AtkStockUsageResource extends Resource
{
    protected static ?string $model = AtkStockUsage::class;

    protected static ?string $navigationLabel = 'Pengeluaran ATK';

    protected static ?string $slug = 'atk/stock-usages';

    protected static ?string $modelLabel = 'Pengeluaran ATK';

    protected static ?string $pluralModelLabel = 'Pengeluaran ATK';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowUpTray;

    protected static string|UnitEnum|null $navigationGroup = 'Alat Tulis Kantor';

    protected static ?string $recordTitleAttribute = 'request_number';

    protected static ?int $navigationSort = 2;

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
        return AtkStockUsageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkStockUsageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkStockUsagesTable::configure($table);
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
            'index' => ListAtkStockUsages::route('/'),
            'approval' => ApprovalAtkStockUsage::route('/approval'),
            'view' => ViewAtkStockUsage::route('/view/{record}'),
        ];
    }
}
