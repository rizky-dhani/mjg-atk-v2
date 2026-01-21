<?php

namespace App\Filament\Resources\AtkStockRequests\Tables;

use App\Exports\AtkStockRequestExport;
use App\Filament\Actions\ApprovalAction;
use App\Filament\Actions\ResubmitAction;
use App\Filament\Resources\AtkStockRequests\Schemas\AtkStockRequestForm;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class AtkStockRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['requester', 'division', 'approval', 'approvalHistory'])
                    ->where('division_id', auth()->user()->division_id)
                    ->orderByDesc('created_at'),
            )
            ->columns([
                TextColumn::make('request_number')
                    ->label('Request Number')
                    ->searchable(),
                TextColumn::make('requester.name')
                    ->label('Requester')
                    ->searchable(),
                TextColumn::make('division.name')
                    ->label('Division')
                    ->searchable(),
                TextColumn::make('approval_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->approval_status)
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->color(
                        fn (string $state): string => match (true) {
                            str_contains(strtolower($state), 'approved') => 'success',
                            str_contains(strtolower($state), 'rejected') => 'danger',
                            default => 'warning',
                        },
                    ),
                TextColumn::make('approved_by.name')
                    ->label('Approved By')
                    ->getStateUsing(fn ($record) => $record->approved_by?->name)
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->modalWidth(Width::SevenExtraLarge)
                    ->authorize(static function ($record) {
                        $user = auth()->user();

                        return $user && $user->id === $record->requester_id;
                    })
                    ->successNotificationTitle('Permintaan stok ATK berhasil diperbarui'),
                Action::make('export')
                    ->label('Export')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('success')
                    ->action(fn ($record) => Excel::download(
                        new AtkStockRequestExport($record->id),
                        'atk_stock_request_'.$record->request_number.'.xlsx'
                    )),
                ApprovalAction::makeApprove()->successNotification(
                    Notification::make()
                        ->title('Permintaan stok ATK berhasil disetujui')
                        ->success(),
                ),
                ApprovalAction::makeReject()->successNotification(
                    Notification::make()
                        ->title('Permintaan stok ATK berhasil ditolak')
                        ->success(),
                ),
                ResubmitAction::make()
                    // Use mountUsing() to fill the form with the record's attributes
                    ->mountUsing(
                        fn (Schema $schema, $record) => $schema->fill(
                            $record->toArray(),
                        ),
                    )
                    ->form(
                        fn (Schema $schema) => AtkStockRequestForm::configure(
                            $schema,
                        ),
                    )
                    ->successNotificationTitle('Permintaan stok ATK berhasil dikirim ulang'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Permintaan stok ATK berhasil dihapus'),
                    BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon(Heroicon::ArrowDownTray)
                        ->color('success')
                        ->action(fn (Collection $records) => Excel::download(
                            new AtkStockRequestExport($records->pluck('id')->toArray()),
                            'atk_stock_requests_'.now()->format('Y-m-d_H-i-s').'.xlsx'
                        )),
                ]),
            ]);
    }
}
