<?php

namespace App\Filament\Resources\AtkDivisionStocks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AtkDivisionStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('division_id')
                    ->required()
                    ->numeric(),
                TextInput::make('item_id')
                    ->required()
                    ->numeric(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('max_stock_limit')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
