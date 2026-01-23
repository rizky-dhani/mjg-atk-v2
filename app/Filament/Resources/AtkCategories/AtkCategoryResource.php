<?php

namespace App\Filament\Resources\AtkCategories;

use App\Filament\Resources\AtkCategories\Pages\ListAtkCategories;
use App\Filament\Resources\AtkCategories\Pages\ViewAtkCategory;
use App\Filament\Resources\AtkCategories\Schemas\AtkCategoryForm;
use App\Filament\Resources\AtkCategories\Tables\AtkCategoriesTable;
use App\Models\AtkCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AtkCategoryResource extends Resource
{
    protected static ?string $model = AtkCategory::class;

    protected static ?string $slug = 'atk/categories';

    protected static ?string $modelLabel = 'Kategori ATK';

    protected static ?string $pluralModelLabel = 'Kategori ATK';

    protected static ?string $navigationLabel = 'Kategori';

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.group.atk');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('filament.navigation.parent_item.inventory_stock');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Tag;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AtkCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAtkCategories::route('/'),
            'view' => ViewAtkCategory::route('/view/{record}'),
        ];
    }
}
