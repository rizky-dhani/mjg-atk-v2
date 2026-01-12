<?php

namespace App\Filament\Actions;

use App\Models\AtkFloatingStock;
use App\Models\UserDivision;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class BulkTransferFloatingStockAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('bulk_transfer_floating_stock')
            ->label('Transfer Selected to Division')
            ->icon('heroicon-o-truck')
            ->color('success')
            ->form([
                Select::make('division_id')
                    ->label('Target Division')
                    ->options(fn () => UserDivision::all()->pluck('name_with_initial', 'id'))
                    ->required()
                    ->searchable(),
                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3),
            ])
            ->action(function (Collection $records, array $data) {
                try {
                    $items = $records->map(fn ($record) => [
                        'item_id' => $record->item_id,
                        'quantity' => $record->current_stock,
                    ])->toArray();

                    AtkFloatingStock::distributeBulkToDivision(
                        $items,
                        (int) $data['division_id'],
                        $data['notes'] ?? null
                    );

                    Notification::make()
                        ->title('Transfer Berhasil')
                        ->body('Berhasil mentransfer '.count($items).' item ke divisi yang dipilih.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Transfer Gagal')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
