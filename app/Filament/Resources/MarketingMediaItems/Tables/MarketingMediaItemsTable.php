<?php

namespace App\Filament\Resources\MarketingMediaItems\Tables;

use App\Models\MarketingMediaCategory;
use App\Models\MarketingMediaDivisionStock;
use App\Models\UserDivision;
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
            ->modifyQueryUsing(fn ($query) => $query->orderBy('category_id')->orderBy('created_at'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->category?->name ?? 'N/A'),
                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('usd')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(MarketingMediaCategory::pluck('name', 'id')),
                Tables\Filters\Filter::make('created_at')
                    ->schema([
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
                EditAction::make()
                    ->using(function ($record, array $data) {
                        $selectedDivisions = $data['divisions'] ?? [];
                        unset($data['divisions']);

                        $record->update($data);

                        // If no divisions selected, add to all marketing divisions
                        if (empty($selectedDivisions)) {
                            $marketingDivisions = UserDivision::where('name', 'like', '%Marketing%')->get();
                        } else {
                            $marketingDivisions = UserDivision::whereIn('id', $selectedDivisions)->get();
                        }

                        foreach ($marketingDivisions as $division) {
                            // Check if stock record already exists for this division
                            $existingStock = MarketingMediaDivisionStock::where([
                                'item_id' => $record->id,
                                'division_id' => $division->id,
                            ])->first();

                            if (! $existingStock) {
                                MarketingMediaDivisionStock::create([
                                    'item_id' => $record->id,
                                    'category_id' => $record->category_id,
                                    'division_id' => $division->id,
                                    'quantity' => 0,
                                    'max_quantity' => 0,
                                ]);
                            }
                        }

                        return $record;
                    }),
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
