<?php

namespace App\Filament\Resources\AtkBudgetings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AtkBudgetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Select::make('division_id')
                    ->relationship('division', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),
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
