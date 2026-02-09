<?php

namespace App\Filament\Resources\AtkFulfillments\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AtkFulfillmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['requester', 'division', 'approval'])
                    ->orderByDesc('created_at'),
            )
            ->columns([
                TextColumn::make('request_number')
                    ->label('Request Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requester.name')
                    ->label('Requester')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('division.name')
                    ->label('Division')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fulfillment_status')
                    ->label('Status Pemenuhan')
                    ->badge()
                    ->formatStateUsing(fn (\App\Enums\FulfillmentStatus $state): string => $state->getLabel())
                    ->color(fn (\App\Enums\FulfillmentStatus $state): string => $state->getColor()),
                TextColumn::make('created_at')
                    ->label('Request Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('fulfillment_status')
                    ->label('Fulfillment Status')
                    ->options(\App\Enums\FulfillmentStatus::class)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, $value): Builder {
                                if ($value === 'fulfilled') {
                                    return $query->whereDoesntHave('atkStockRequestItems', function ($q) {
                                        $q->whereRaw('received_quantity < quantity');
                                    });
                                } elseif ($value === 'partially_fulfilled') {
                                    return $query->whereHas('atkStockRequestItems', function ($q) {
                                        $q->where('received_quantity', '>', 0);
                                    })->whereHas('atkStockRequestItems', function ($q) {
                                        $q->whereRaw('received_quantity < quantity');
                                    });
                                } elseif ($value === 'pending') {
                                    return $query->whereDoesntHave('atkStockRequestItems', function ($q) {
                                        $q->where('received_quantity', '>', 0);
                                    });
                                }

                                return $query;
                            }
                        );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                // No bulk actions needed for now
            ]);
    }
}
