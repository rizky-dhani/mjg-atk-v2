<?php

namespace App\Filament\Resources\AtkItems\Schemas;

use App\Models\AtkCategory;
use App\Models\UserDivision;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtkItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Item Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        Select::make('category_id')
                            ->label('Category')
                            ->options(AtkCategory::all()->pluck('name', 'id'))
                            ->required(),
                        TextInput::make('unit_of_measure')
                            ->label('Unit of Measure')
                            ->required(),
                        Select::make('division_id')
                            ->multiple()
                            ->label('Divisions')
                            ->helperText('Select divisions to add this item to. Leave empty to add to all divisions.')
                            ->options(UserDivision::pluck('name', 'id'))
                            ->preload()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
