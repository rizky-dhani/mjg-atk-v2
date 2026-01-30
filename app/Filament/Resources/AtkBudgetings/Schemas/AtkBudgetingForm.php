<?php

namespace App\Filament\Resources\AtkBudgetings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AtkBudgetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Select::make('division_id')
                    ->relationship(
                        name: 'division',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => auth()->user()->isSuperAdmin()
                            ? $query
                            : $query->whereIn('id', auth()->user()->divisions->pluck('id'))
                    )
                    ->default(auth()->user()->divisions()->first()?->id)
                    ->required()
                    ->preload()
                    ->searchable()
                    ->disabled(fn () => ! auth()->user()->isSuperAdmin() && auth()->user()->divisions()->count() === 1)
                    ->dehydrated(true),
                TextInput::make('budget_amount')
                    ->label('Budget Amount')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('fiscal_year')
                    ->label('Fiscal Year')
                    ->required()
                    ->numeric()
                    ->minValue(2004)
                    ->maxValue(date('Y') + 5)
                    ->default(date('Y')),
            ]);
    }
}
