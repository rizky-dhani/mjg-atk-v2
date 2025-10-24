<?php

namespace App\Filament\Resources\MarketingMediaStockRequests;

use App\Filament\Resources\MarketingMediaStockRequests\Pages\ApprovalMarketingMediaStockRequest;
use App\Filament\Resources\MarketingMediaStockRequests\Pages\ListMarketingMediaStockRequests;
use App\Filament\Resources\MarketingMediaStockRequests\Pages\ViewMarketingMediaStockRequest;
use App\Filament\Resources\MarketingMediaStockRequests\RelationManagers\MarketingMediaStockRequestItemsRelationManager;
use App\Filament\Resources\MarketingMediaStockRequests\Schemas\MarketingMediaStockRequestForm;
use App\Filament\Resources\MarketingMediaStockRequests\Schemas\MarketingMediaStockRequestInfolist;
use App\Filament\Resources\MarketingMediaStockRequests\Tables\MarketingMediaStockRequestsTable;
use App\Models\MarketingMediaStockRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MarketingMediaStockRequestResource extends Resource
{
    protected static ?string $model = MarketingMediaStockRequest::class;

    protected static ?string $navigationLabel = 'Permintaan Marketing Media';

    protected static ?string $slug = 'marketing-media/stock-requests';

    protected static ?string $modelLabel = 'Permintaan Marketing Media';

    protected static ?string $pluralModelLabel = 'Permintaan Marketing Media';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowDownTray;

    protected static string|UnitEnum|null $navigationGroup = 'Marketing Media';

    protected static ?string $recordTitleAttribute = 'request_number';

    public static function form(Schema $schema): Schema
    {
        return MarketingMediaStockRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MarketingMediaStockRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketingMediaStockRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MarketingMediaStockRequestItemsRelationManager::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketingMediaStockRequests::route('/'),
            'view' => ViewMarketingMediaStockRequest::route('/view/{record}'),
            'approval' => ApprovalMarketingMediaStockRequest::route('/approval'),
        ];
    }
}
