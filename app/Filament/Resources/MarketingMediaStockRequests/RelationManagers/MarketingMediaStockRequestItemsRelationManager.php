<?php

namespace App\Filament\Resources\MarketingMediaStockRequests\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\MarketingMediaCategory;
use App\Models\MarketingMediaItem;

class MarketingMediaStockRequestItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'marketingMediaStockRequestItems';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->options(MarketingMediaCategory::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('item_id', null);
                            }),
                        Select::make('item_id')
                            ->label('Item')
                            ->options(function (callable $get) {
                                $categoryId = $get('category_id');
                                if (! $categoryId) {
                                    return MarketingMediaItem::all()->pluck('name', 'id');
                                }

                                return MarketingMediaItem::where('category_id', $categoryId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('notes')
                            ->label('Notes')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item.name')
            ->columns([
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('item.name')
                    ->label('Item')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
