<?php

namespace App\Filament\Actions;

use App\Imports\AtkDivisionStockImport;
use App\Models\UserDivision;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class ImportStockAction
{
    public static function make(): Action
    {
        return Action::make('import-stock')
            ->label('Import Stock')
            ->button()
            ->form([
                Select::make('division_id')
                    ->label('Division')
                    ->options(function () {
                        // Load all available divisions from user_divisions with format: "initial - name"
                        return UserDivision::all()->mapWithKeys(function ($division) {
                            return [$division->id => $division->initial . ' - ' . $division->name];
                        })->toArray();
                    })
                    ->required()
                    ->searchable(),
                
                FileUpload::make('excel_file')
                    ->label('Excel File')
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                    ->required()
                    ->disk('local')
                    ->directory('imports')
                    ->visibility('private'),
            ])
            ->action(function (array $data) {
                try {
                    $import = new AtkDivisionStockImport($data['division_id']);
                    Excel::import($import, $data['excel_file'], 'local');

                    Notification::make()
                        ->title('Stock imported successfully')
                        ->body("Processed: {$import->processedCount} records. Skipped: {$import->skippedCount} records.")
                        ->success()
                        ->send();
                } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                    $failures = $e->failures();
                    $errorMessages = [];
                    foreach ($failures as $failure) {
                        $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
                    }

                    Notification::make()
                        ->title('Validation Error during import')
                        ->body(implode('<br>', array_slice($errorMessages, 0, 5)) . (count($errorMessages) > 5 ? '<br>...' : ''))
                        ->danger()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error importing stock')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}