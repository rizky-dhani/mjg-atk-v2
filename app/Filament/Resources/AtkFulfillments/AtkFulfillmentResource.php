<?php

namespace App\Filament\Resources\AtkFulfillments;

use App\Filament\Resources\AtkFulfillments\Pages\ListAtkFulfillments;
use App\Filament\Resources\AtkFulfillments\Pages\ViewAtkFulfillment;
use App\Filament\Resources\AtkFulfillments\Schemas\AtkFulfillmentForm;
use App\Filament\Resources\AtkFulfillments\Schemas\AtkFulfillmentInfolist;
use App\Filament\Resources\AtkFulfillments\Tables\AtkFulfillmentsTable;
use App\Models\AtkFulfillment;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AtkFulfillmentResource extends Resource
{
    protected static ?string $model = AtkFulfillment::class;

    protected static ?string $navigationLabel = 'Pemenuhan Stok';

    protected static ?string $slug = 'atk/fulfillments';

    protected static ?string $modelLabel = 'Pemenuhan Stok';

    protected static ?string $pluralModelLabel = 'Pemenuhan Stok';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Alat Tulis Kantor';

    protected static ?string $recordTitleAttribute = 'request_number';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('approval', fn ($query) => $query->where('status', 'approved'))
            ->where('status', \App\Enums\AtkStockRequestStatus::Published);
    }

    public static function form(Schema $schema): Schema
    {
        return AtkFulfillmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkFulfillmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkFulfillmentsTable::configure($table);
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
            'index' => ListAtkFulfillments::route('/'),
            'view' => ViewAtkFulfillment::route('/{record}'),
        ];
    }
}
