<?php

namespace App\Filament\Resources\AtkRequestFromFloatingStocks\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AtkRequestFromFloatingStockItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'atkRequestFromFloatingStockItems';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn ($query) => $query->with(['item']))
            ->columns([
                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity Requested')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ]);
    }
}
