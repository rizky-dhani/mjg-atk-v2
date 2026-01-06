<?php

namespace App\Filament\Actions;

use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;

class BulkMoveToFloatingAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('bulk_move_to_floating')
            ->label('Move All to Floating Stock')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn () => auth()->user()->isGA())
            ->action(function (Collection $records) {
                $processedCount = 0;
                $skippedCount = 0;

                foreach ($records as $record) {
                    if ($record->current_stock > 0) {
                        try {
                            $record->moveToFloating($record->current_stock);
                            $processedCount++;
                        } catch (\Exception $e) {
                            $skippedCount++;
                        }
                    } else {
                        $skippedCount++;
                    }
                }

                Notification::make()
                    ->title('Bulk move completed')
                    ->body("Successfully moved {$processedCount} items. Skipped {$skippedCount} items.")
                    ->success()
                    ->send();
            });
    }
}
