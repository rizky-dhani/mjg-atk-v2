<?php

namespace App\Filament\Resources\MarketingMediaItems\Schemas;

use App\Models\MarketingMediaCategory;
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
                        Textarea::make('description')
                            ->nullable()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->options(MarketingMediaCategory::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('unit')
                            ->required()
                            ->maxLength(50)
                            ->default('piece'),
                        TextInput::make('unit_price')
                            ->label('Unit Price')
                            ->required()
                            ->numeric()
                            ->prefix()
                            ->default(0.00)
                            ->step(0.01),
                    ])
                    ->columns(2),
            ]);
    }
}
