<?php

namespace App\Filament\Resources\MarketingMediaItems\Schemas;

use App\Models\MarketingMediaCategory;
use App\Models\UserDivision;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MarketingMediaItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Enter the basic details for the marketing media item.')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->options(MarketingMediaCategory::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('division_id')
                            ->multiple()
                            ->label('Marketing Divisions')
                            ->helperText('Select marketing divisions to add this item to. Leave empty to add to all marketing divisions.')
                            ->options(UserDivision::where('name', 'like', '%Marketing%')->pluck('name', 'id'))
                            ->preload()
                            ->columnSpanFull(),
                        TextInput::make('unit_of_measure')
                            ->required()
                            ->maxLength(50)
                            ->default('piece'),
                        Textarea::make('description')
                            ->nullable()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
