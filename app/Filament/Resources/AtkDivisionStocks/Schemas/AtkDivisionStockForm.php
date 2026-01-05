<?php

namespace App\Filament\Resources\AtkDivisionStocks\Schemas;

use App\Models\UserDivision;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AtkDivisionStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('division_id')
                    ->relationship('division', 'name')
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn (UserDivision $record): string => $record->getNameWithInitialAttribute()),
                Select::make('item_id')
                    ->relationship('item', 'name')
                    ->required(),
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
