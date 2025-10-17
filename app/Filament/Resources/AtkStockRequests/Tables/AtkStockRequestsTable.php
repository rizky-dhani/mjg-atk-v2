<?php

namespace App\Filament\Resources\AtkStockRequests\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Schemas\Components\Form;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Actions\ApprovalAction;
use App\Filament\Actions\ResubmitAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AtkStockRequests\AtkStockRequestResource;
use App\Filament\Resources\AtkStockRequests\Schemas\AtkStockRequestForm;

class AtkStockRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ->formatStateUsing(function ($record) {
                        $approval = $record->approval;
                        if (!$approval) {
                            return 'Pending';
                        }
                        
                        // Get the latest approval step approval
                        $latestApproval = $approval->approvalStepApprovals()
                            ->with('user')
                            ->latest('approved_at')
                            ->first();
                        
                        if ($latestApproval) {
                            $status = ucfirst($latestApproval->status);
                            $approver = $latestApproval->user ? $latestApproval->user->name : 'Unknown';
                            return "{$status} by {$approver}";
                        }
                        
                        return $approval->status ? ucfirst($approval->status) : 'Pending';
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'approved') => 'success',
                        str_contains($state, 'rejected') => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->authorize(static function ($record) {
                        $user = auth()->user();
                        return $user && $user->id === $record->requester_id;
                    }),
                ApprovalAction::makeApprove(),
                ApprovalAction::makeReject(),
                ResubmitAction::make()
                    // Use mountUsing() to fill the form with the record's attributes
                    ->mountUsing(fn(Schema $schema, $record) => $schema->fill($record->toArray()))
                    ->form(fn(Schema $schema) => AtkStockRequestForm::configure($schema))
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
