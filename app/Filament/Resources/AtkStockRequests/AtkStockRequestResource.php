<?php

namespace App\Filament\Resources\AtkStockRequests;

use App\Filament\Resources\AtkStockRequests\Pages\ApprovalAtkStockRequest;
use App\Filament\Resources\AtkStockRequests\Pages\ListAtkStockRequests;
use App\Filament\Resources\AtkStockRequests\Pages\ViewAtkStockRequest;
use App\Filament\Resources\AtkStockRequests\Schemas\AtkStockRequestForm;
use App\Filament\Resources\AtkStockRequests\Schemas\AtkStockRequestInfolist;
use App\Filament\Resources\AtkStockRequests\Tables\AtkStockRequestsTable;
use App\Models\AtkStockRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AtkStockRequestResource extends Resource
{
    protected static ?string $model = AtkStockRequest::class;

    protected static ?string $navigationLabel = 'Permintaan ATK';

    protected static ?string $slug = 'atk/stock-requests';

    protected static ?string $modelLabel = 'Permintaan ATK';

    protected static ?string $pluralModelLabel = 'Permintaan ATK';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowDownTray;

    protected static string|UnitEnum|null $navigationGroup = 'Alat Tulis Kantor';

    protected static ?string $recordTitleAttribute = 'request_number';

    public static function form(Schema $schema): Schema
    {
        return AtkStockRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkStockRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkStockRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\AtkStockRequests\RelationManagers\AtkStockRequestItemsRelationManager::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAtkStockRequests::route('/'),
            'view' => ViewAtkStockRequest::route('/view/{record}'),
            'approval' => ApprovalAtkStockRequest::route('/approval'),
        ];
    }
}
