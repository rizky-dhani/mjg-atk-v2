<?php

namespace App\Filament\Resources\MarketingMediaCategories;

use App\Models\MarketingMediaCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MarketingMediaCategoryResource extends Resource
{
    protected static ?string $model = MarketingMediaCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Folder;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.group.marketing_media');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('filament.navigation.parent_item.inventory_stock');
    }

    protected static ?string $navigationLabel = 'Kategori';

    protected static ?string $modelLabel = 'Kategori Marketing Media';

    protected static ?string $pluralLabel = 'Kategori Marketing Media';

    protected static ?string $slug = 'marketing-media-categories';

    public static function form(Schema $schema): Schema
    {
        return Schemas\MarketingMediaCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\MarketingMediaCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingMediaCategories::route('/'),
            'view' => Pages\ViewMarketingMediaCategory::route('/view/{record}'),
        ];
    }
}
