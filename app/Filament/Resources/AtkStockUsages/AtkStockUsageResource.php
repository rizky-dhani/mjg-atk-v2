<?php

namespace App\Filament\Resources\AtkStockUsages;

use App\Filament\Resources\AtkStockUsages\Pages\CreateAtkStockUsage;
use App\Filament\Resources\AtkStockUsages\Pages\EditAtkStockUsage;
use App\Filament\Resources\AtkStockUsages\Pages\ListAtkStockUsages;
use App\Filament\Resources\AtkStockUsages\Pages\ViewAtkStockUsage;
use App\Filament\Resources\AtkStockUsages\Schemas\AtkStockUsageForm;
use App\Filament\Resources\AtkStockUsages\Schemas\AtkStockUsageInfolist;
use App\Filament\Resources\AtkStockUsages\Tables\AtkStockUsagesTable;
use App\Models\AtkStockUsage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AtkStockUsageResource extends Resource
{
    protected static ?string $model = AtkStockUsage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AtkStockUsageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkStockUsageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkStockUsagesTable::configure($table);
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
            'index' => ListAtkStockUsages::route('/'),
            'create' => CreateAtkStockUsage::route('/create'),
            'view' => ViewAtkStockUsage::route('/{record}'),
            'edit' => EditAtkStockUsage::route('/{record}/edit'),
        ];
    }
}
