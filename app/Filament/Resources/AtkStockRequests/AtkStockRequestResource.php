<?php

namespace App\Filament\Resources\AtkStockRequests;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\AtkStockRequest;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\AtkStockRequests\Pages\EditAtkStockRequest;
use App\Filament\Resources\AtkStockRequests\Pages\ViewAtkStockRequest;
use App\Filament\Resources\AtkStockRequests\Pages\ListAtkStockRequests;
use App\Filament\Resources\AtkStockRequests\Pages\CreateAtkStockRequest;
use App\Filament\Resources\AtkStockRequests\Schemas\AtkStockRequestForm;
use App\Filament\Resources\AtkStockRequests\Tables\AtkStockRequestsTable;
use App\Filament\Resources\AtkStockRequests\Schemas\AtkStockRequestInfolist;

class AtkStockRequestResource extends Resource
{
    protected static ?string $model = AtkStockRequest::class;
    protected static ?string $navigationLabel = 'Permintaan ATK';
    protected static ?string $slug = 'atk/stock-requests';    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowDownTray;
    protected static string | UnitEnum | null $navigationGroup = 'Alat Tulis Kantor';
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

    public static function getPages(): array
    {
        return [
            'index' => ListAtkStockRequests::route('/'),
            'view' => ViewAtkStockRequest::route('/view/{record}'),
        ];
    }
}
