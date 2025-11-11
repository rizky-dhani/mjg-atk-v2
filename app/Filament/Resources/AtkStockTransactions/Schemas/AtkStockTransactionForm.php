<?php

namespace App\Filament\Resources\AtkStockTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AtkStockTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('division_id')
                    ->relationship('division', 'name')
                    ->required(),
                Select::make('item_id')
                    ->relationship('item', 'name')
                    ->required(),
                Select::make('type')
                    ->options([
                        'request' => 'Request',
                        'usage' => 'Usage',
                        'adjustment' => 'Adjustment',
                        'transfer' => 'Transfer',
                    ])
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('unit_cost')
                    ->required()
                    ->numeric()
                    ->step(0.01),
                TextInput::make('total_cost')
                    ->required()
                    ->numeric()
                    ->step(0.01),
                TextInput::make('mac_snapshot')
                    ->required()
                    ->numeric()
                    ->step(0.01),
                TextInput::make('balance_snapshot')
                    ->required()
                    ->numeric(),
            ]);
    }
}