<?php

namespace App\Filament\Resources\MarketingMediaDivisionStocks\RelationManagers;

use App\Models\MarketingMediaStockUsage;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarketingMediaStockUsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'marketingMediaStockUsages';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        // Define form components for creating/editing related records if needed
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('request_number')
            ->columns([
                TextColumn::make('request_number')
                    ->label('Usage Number')
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
                TextColumn::make('request_type')
                    ->label('Usage Type')
                    ->sortable(),
                TextColumn::make('marketingMediaStockUsageItems.quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('approval.status')
                    ->label('Status')
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'pending' => 'warning',
                            default => 'gray',
                        };
                    }),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Add filters as needed
            ])
            ->headerActions([
                // Add header actions as needed
            ])
            ->recordActions([
                // Add row actions as needed
            ])
            ->bulkActions([
                // Add bulk actions as needed
            ])
            ->modifyQueryUsing(function (Builder $query) {
                // Filter usages that contain items related to this division stock's item
                $record = $this->getOwnerRecord();
                $itemId = $record->item_id;
                
                $query->whereHas('marketingMediaStockUsageItems', function ($q) use ($itemId) {
                    $q->where('item_id', $itemId);
                });
            });
    }
}