<?php

namespace App\Filament\Resources\AtkStockRequests;

use App\Filament\Resources\AtkStockRequests\Pages\CreateAtkStockRequest;
use App\Filament\Resources\AtkStockRequests\Pages\EditAtkStockRequest;
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

class AtkStockRequestResource extends Resource
{
    protected static ?string $model = AtkStockRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAtkStockRequests::route('/'),
            'create' => CreateAtkStockRequest::route('/create'),
            'view' => ViewAtkStockRequest::route('/{record}'),
            'edit' => EditAtkStockRequest::route('/{record}/edit'),
        ];
    }
}
