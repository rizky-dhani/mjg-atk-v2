<?php

namespace App\Filament\Resources\AtkDivisionStocks\RelationManagers;

use App\Models\AtkStockTransaction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AtkStockTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockTransactions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('Transaction ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'request' => 'success',
                        'usage' => 'danger',
                        'adjustment' => 'warning',
                        'transfer' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('mac_snapshot')
                    ->label('MAC (Snapshot)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('balance_snapshot')
                    ->label('Balance (Snapshot)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}