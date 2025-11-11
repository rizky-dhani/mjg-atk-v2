<?php

namespace App\Filament\Resources\AtkBudgetings\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AtkStockUsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'relatedStockUsages';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('request_number')
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->label('Request Number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_items')
                    ->label('Total Items')
                    ->getStateUsing(function ($record) {
                        return $record->items->count();
                    }),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Total Cost')
                    ->getStateUsing(function ($record) {
                        // Calculate total cost based on the items and their moving average cost
                        $totalCost = 0;
                        foreach ($record->items as $item) {
                            $stock = $record->division->atkDivisionStocks()
                                ->where('item_id', $item->item_id)
                                ->first();
                            if ($stock) {
                                $totalCost += $stock->moving_average_cost * $item->quantity;
                            }
                        }
                        return $totalCost;
                    })
                    ->money('IDR'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}