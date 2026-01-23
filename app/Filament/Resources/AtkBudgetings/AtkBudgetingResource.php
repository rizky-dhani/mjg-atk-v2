<?php

namespace App\Filament\Resources\AtkBudgetings;

use App\Filament\Resources\AtkBudgetings\Pages\ListAtkBudgetings;
use App\Filament\Resources\AtkBudgetings\Pages\ViewAtkBudgeting;
use App\Filament\Resources\AtkBudgetings\Schemas\AtkBudgetingForm;
use App\Filament\Resources\AtkBudgetings\Schemas\AtkBudgetingInfolist;
use App\Filament\Resources\AtkBudgetings\Tables\AtkBudgetingsTable;
use App\Models\AtkBudgeting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AtkBudgetingResource extends Resource
{
    protected static ?string $model = AtkBudgeting::class;

    protected static ?string $navigationLabel = 'Anggaran ATK';

    protected static ?string $slug = 'atk/budgetings';

    protected static ?string $modelLabel = 'Anggaran ATK';

    protected static ?string $pluralModelLabel = 'Anggaran ATK';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.group.settings');
    }

    public static function form(Schema $schema): Schema
    {
        return AtkBudgetingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AtkBudgetingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AtkBudgetingsTable::configure($table);
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
            'index' => ListAtkBudgetings::route('/'),
            'view' => ViewAtkBudgeting::route('/view/{record}'),
        ];
    }
}
