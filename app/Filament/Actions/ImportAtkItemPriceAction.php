<?php

namespace App\Filament\Actions;

use App\Imports\AtkItemPriceImport;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class ImportAtkItemPriceAction
{
    public static function make(): Action
    {
        return Action::make('import-atk-item-price')
            ->label('Import Harga')
            ->button()
            ->color('success')
            ->form([
                DatePicker::make('effective_date')
                    ->label('Tanggal Efektif')
                    ->default(now())
                    ->required(),

                FileUpload::make('excel_file')
                    ->label('File Excel')
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                    ->required()
                    ->disk('local')
                    ->directory('imports')
                    ->visibility('private'),
            ])
            ->action(function (array $data) {
                try {
                    $import = new AtkItemPriceImport($data['effective_date']);
                    Excel::import($import, $data['excel_file'], 'local');

                    Notification::make()
                        ->title('Harga item berhasil diimpor')
                        ->body("Diproses: {$import->processedCount} baris. Dilewati: {$import->skippedCount} baris.")
                        ->success()
                        ->send();
                } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                    $failures = $e->failures();
                    $errorMessages = [];
                    foreach ($failures as $failure) {
                        $errorMessages[] = "Baris {$failure->row()}: ".implode(', ', $failure->errors());
                    }

                    Notification::make()
                        ->title('Kesalahan Validasi saat impor')
                        ->body(implode('<br>', array_slice($errorMessages, 0, 5)).(count($errorMessages) > 5 ? '<br>...' : ''))
                        ->danger()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Gagal mengimpor harga')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
