<?php

namespace App\Filament\Resources\AtkStockUsages;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\AtkStockUsage;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\AtkStockUsages\Pages\EditAtkStockUsage;
use App\Filament\Resources\AtkStockUsages\Pages\ViewAtkStockUsage;
use App\Filament\Resources\AtkStockUsages\Pages\ListAtkStockUsages;
use App\Filament\Resources\AtkStockUsages\Pages\CreateAtkStockUsage;
use App\Filament\Resources\AtkStockUsages\Schemas\AtkStockUsageForm;
use App\Filament\Resources\AtkStockUsages\Tables\AtkStockUsagesTable;
use App\Filament\Resources\AtkStockUsages\Schemas\AtkStockUsageInfolist;

class AtkStockUsageResource extends Resource
{
    protected static ?string $model = AtkStockUsage::class;
    protected static ?string $navigationLabel = 'Pengeluaran ATK';
    protected static ?string $slug = 'atk/stock-usages';    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowUpTray;
    protected static string | UnitEnum | null $navigationGroup = 'Alat Tulis Kantor';
    protected static ?string $recordTitleAttribute = 'usage_number';

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
