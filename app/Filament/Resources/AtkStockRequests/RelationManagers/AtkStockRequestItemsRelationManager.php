<?php

namespace App\Filament\Resources\AtkStockRequests\RelationManagers;

use Filament\Tables;
use App\Models\AtkItem;
use App\Models\AtkCategory;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class AtkStockRequestItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'atkStockRequestItems';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity_requested')
                    ->label('Quantity Requested')
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}