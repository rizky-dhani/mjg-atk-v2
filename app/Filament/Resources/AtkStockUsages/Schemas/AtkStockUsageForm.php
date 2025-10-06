<?php

namespace App\Filament\Resources\AtkStockUsages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AtkStockUsageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('usage_number')
                    ->required(),
                TextInput::make('requester_id')
                    ->required()
                    ->numeric(),
                TextInput::make('division_id')
                    ->required()
                    ->numeric(),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
