<?php

namespace App\Filament\Resources\MarketingMediaItems\Tables;

use App\Models\MarketingMediaCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class MarketingMediaItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->category?->name ?? 'N/A'),
                Tables\Columns\TextColumn::make('unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(MarketingMediaCategory::pluck('name', 'id')),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->placeholder('Start date'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->placeholder('End date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['created_from'],
                            fn ($query, $date) => $query->whereDate('created_at', '>=', $date),
                        )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created from '.date('M j, Y', strtotime($data['created_from']));
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until '.date('M j, Y', strtotime($data['created_until']));
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
