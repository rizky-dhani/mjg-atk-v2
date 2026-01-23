<?php

namespace App\Filament\Resources\AtkItems;

use App\Filament\Resources\AtkItems\Pages\ListAtkItems;
use App\Filament\Resources\AtkItems\Pages\ViewAtkItem;
use App\Filament\Resources\AtkItems\Schemas\AtkItemForm;
use App\Filament\Resources\AtkItems\Schemas\AtkItemInfolist;
use App\Filament\Resources\AtkItems\Tables\AtkItemsTable;
use App\Models\AtkItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AtkItemResource extends Resource
{
    protected static ?string $model = AtkItem::class;

    protected static ?string $slug = 'atk/items';

    protected static ?string $modelLabel = 'Item ATK';

    protected static ?string $pluralModelLabel = 'Item ATK';

    protected static ?string $navigationLabel = 'Item';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.group.atk');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('filament.navigation.parent_item.inventory_stock');
    }

    public static function form(Schema $schema): Schema
    {
        return AtkItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAtkItems::route('/'),
            'view' => ViewAtkItem::route('/view/{record}'),
        ];
    }
}
