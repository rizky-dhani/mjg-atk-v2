<?php

namespace App\Filament\Actions;

use App\Imports\AtkDivisionStockImport;
use App\Models\UserDivision;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;
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
                $import = new AtkDivisionStockImport($data['division_id']);
                Excel::import($import, $data['excel_file']);
                
                return redirect()->back();
            });
    }
}