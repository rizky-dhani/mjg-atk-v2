<?php

namespace App\Filament\Resources\AtkStockRequests\RelationManagers;

use App\Enums\AtkStockRequestItemStatus;
use App\Models\AtkStockRequestItem;
use App\Services\FulfillmentService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

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
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Requested')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('received_quantity')
                    ->label('Received')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining_quantity')
                    ->label('Remaining')
                    ->numeric()
                    ->state(fn (AtkStockRequestItem $record): int => $record->remaining_quantity)
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (AtkStockRequestItemStatus $state): string => $state->getLabel())
                    ->color(fn (AtkStockRequestItemStatus $state): string => $state->getColor()),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(AtkStockRequestItemStatus::class),
            ])
            ->recordActions([
                Action::make('store_stock')
                    ->label('Simpan Stok')
                    ->icon(Heroicon::ArchiveBoxArrowDown)
                    ->color('success')
                    ->visible(fn (AtkStockRequestItem $record): bool => $record->request->approval?->status === 'approved' &&
                        ! $record->isFullyReceived()
                    )
                    ->form(fn (AtkStockRequestItem $record) => [
                        TextInput::make('qty')
                            ->label('Jumlah Diterima')
                            ->numeric()
                            ->required()
                            ->maxValue($record->remaining_quantity)
                            ->minValue(1),
                    ])
                    ->action(function (AtkStockRequestItem $record, array $data): void {
                        try {
                            app(FulfillmentService::class)->receiveItem($record, $data['qty']);
                            Notification::make()
                                ->title('Stok Berhasil Disimpan')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Menyimpan Stok')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_store_stock')
                        ->label('Simpan Stok Terpilih')
                        ->icon(Heroicon::ArchiveBoxArrowDown)
                        ->color('success')
                        ->visible(fn (RelationManager $livewire): bool => $livewire->getOwnerRecord()->approval?->status === 'approved'
                        )
                        ->action(function (Collection $records): void {
                            $fulfillmentService = app(FulfillmentService::class);
                            $successCount = 0;

                            foreach ($records as $record) {
                                if (! $record->isFullyReceived()) {
                                    $fulfillmentService->receiveItem($record, $record->remaining_quantity);
                                    $successCount++;
                                }
                            }

                            if ($successCount > 0) {
                                Notification::make()
                                    ->title("$successCount Item Berhasil Disimpan")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
