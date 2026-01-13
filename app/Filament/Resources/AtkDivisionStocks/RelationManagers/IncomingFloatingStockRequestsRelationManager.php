<?php

namespace App\Filament\Resources\AtkDivisionStocks\RelationManagers;

use App\Filament\Resources\AtkRequestFromFloatingStocks\AtkRequestFromFloatingStockResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IncomingFloatingStockRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'incomingFloatingStockRequests';

    protected static ?string $title = 'Permintaan Stok Umum';

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('request_number')
            ->columns([
                TextColumn::make('request.request_number')
                    ->label('Nomor Permintaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('request.approval_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->request->approval_status)
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->color(
                        fn (string $state): string => match (true) {
                            str_contains(strtolower($state), 'approved') => 'success',
                            str_contains(strtolower($state), 'rejected') => 'danger',
                            default => 'warning',
                        },
                    ),
                TextColumn::make('request.created_at')
                    ->label('Tanggal Permintaan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Read-only
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => AtkRequestFromFloatingStockResource::getUrl('index', [
                        'tableSearch' => $record->request->request_number,
                    ])),
            ])
            ->toolbarActions([
                // Read-only
            ]);
    }
}