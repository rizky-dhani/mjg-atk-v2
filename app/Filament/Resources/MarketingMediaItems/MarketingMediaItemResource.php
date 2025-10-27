<?php

namespace App\Filament\Resources\MarketingMediaItems;

use App\Filament\Resources\MarketingMediaItems\Tables\MarketingMediaItemsTable;
use App\Models\MarketingMediaItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MarketingMediaItemResource extends Resource
{
    protected static ?string $model = MarketingMediaItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Document;

    protected static ?string $navigationParentItem = 'Stok Inventaris';

    protected static ?string $navigationLabel = 'Item';

    protected static ?string $modelLabel = 'Item Marketing Media';

    protected static ?string $pluralModelLabel = 'Item Marketing Media';

    protected static ?string $slug = 'marketing-media-items';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing Media';

    public static function form(Schema $schema): Schema
    {
        return Schemas\MarketingMediaItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketingMediaItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingMediaItems::route('/'),
            'view' => Pages\ViewMarketingMediaItem::route('/view/{record}'),
        ];
    }
}
