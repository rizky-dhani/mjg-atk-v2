<?php

namespace App\Filament\Actions;

use App\Models\UserDivision;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
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
                    ->options(UserDivision::all()->mapWithKeys(fn ($division) => [$division->id => "{$division->initial} - {$division->name}"])->toArray())
                    ->required()
                    ->searchable(),
            ])
            ->action(function (Collection $records, array $data) {
                $processedCount = 0;
                $skippedCount = 0;
                $divisionId = (int) $data['division_id'];

                foreach ($records as $record) {
                    if ($record->current_stock > 0) {
                        try {
                            $record->distributeToDivision($divisionId, $record->current_stock);
                            $processedCount++;
                        } catch (\Exception $e) {
                            $skippedCount++;
                        }
                    } else {
                        $skippedCount++;
                    }
                }

                Notification::make()
                    ->title('Bulk transfer completed')
                    ->body("Successfully transferred {$processedCount} items. Skipped {$skippedCount} items.")
                    ->success()
                    ->send();
            });
    }
}
