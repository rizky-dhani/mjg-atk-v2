<?php

namespace App\Filament\Resources\MarketingMediaStockUsages;

use App\Filament\Resources\MarketingMediaStockUsages\Pages\ApprovalMarketingMediaStockUsage;
use App\Filament\Resources\MarketingMediaStockUsages\Pages\ListMarketingMediaStockUsages;
use App\Filament\Resources\MarketingMediaStockUsages\Pages\ViewMarketingMediaStockUsage;
use App\Filament\Resources\MarketingMediaStockUsages\Schemas\MarketingMediaStockUsageForm;
use App\Filament\Resources\MarketingMediaStockUsages\Schemas\MarketingMediaStockUsageInfolist;
use App\Filament\Resources\MarketingMediaStockUsages\Tables\MarketingMediaStockUsagesTable;
use App\Models\MarketingMediaStockUsage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MarketingMediaStockUsageResource extends Resource
{
    protected static ?string $model = MarketingMediaStockUsage::class;

    protected static ?string $navigationLabel = 'Pengeluaran Marketing Media';

    protected static ?string $slug = 'marketing-media/stock-usages';

    protected static ?string $modelLabel = 'Pengeluaran Marketing Media';

    protected static ?string $pluralModelLabel = 'Pengeluaran Marketing Media';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowUpTray;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.group.marketing_media');
    }

    protected static ?string $recordTitleAttribute = 'request_number';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return MarketingMediaStockUsageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MarketingMediaStockUsageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketingMediaStockUsagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\MarketingMediaStockUsages\RelationManagers\MarketingMediaStockUsageItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketingMediaStockUsages::route('/'),
            'approval' => ApprovalMarketingMediaStockUsage::route('/approval'),
            'view' => ViewMarketingMediaStockUsage::route('/view/{record}'),
        ];
    }
}
