<?php

namespace App\Filament\Resources\AtkStockUsages\Pages;

use App\Filament\Actions\ApprovalAction;
use App\Filament\Resources\AtkStockUsages\AtkStockUsageResource;
use App\Models\Approval;
use App\Models\ApprovalFlowStep;
use App\Models\AtkStockUsage;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ApprovalAtkStockUsage extends ListRecords
{
    protected static string $resource = AtkStockUsageResource::class;

    protected static ?string $slug = 'atk/stock-usages/approval';

    protected static ?string $navigationLabel = 'Approval Penggunaan ATK';

    protected static string|UnitEnum|null $navigationGroup = 'Alat Tulis Kantor';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCheck;

    protected static ?string $title = 'Approval Penggunaan ATK';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        // Find approval flow steps that match the user's role and division
        $matchingStepIds = ApprovalFlowStep::whereHas('role', function ($query) use ($user) {
            $query->whereIn('id', $user->roles->pluck('id'));
        })
            ->where(function ($query) use ($user) {
                // Step is available for the user's own division OR for specific division
                $query
                    ->whereNull('division_id')
                    ->orWhere('division_id', $user->division_id);
            })
            ->pluck('id');

        // Get count of approval records that are pending and have steps matching the user's permissions
        $count = Approval::whereIn('current_step', $matchingStepIds)
            ->where('status', 'pending')
            ->where('approvable_type', (new AtkStockUsage)->getMorphClass()) // Ensure it's for ATK Stock Usages
            ->count();

        return (string) $count;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $this->getQuery())
            ->columns([
                TextColumn::make('request_number')
                    ->label('Nomor Penggunaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requester.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('division.name')
                    ->label('Divisi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('approval.status')
                    ->label('Status Approval')
                    ->badge()
                    ->color(
                        fn (string $state): string => match ($state) {
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'pending' => 'warning',
                            default => 'gray',
                        },
                    ),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->actions([
                ApprovalAction::makeApprove()->successNotification(
                    Notification::make()
                        ->title('Penggunaan ATK berhasil disetujui!')
                        ->success(),
                ),
                ApprovalAction::makeReject()->successNotification(
                    Notification::make()
                        ->title('Penggunaan ATK berhasil ditolak!')
                        ->success(),
                ),
            ]);
    }

    protected function getQuery(): Builder
    {
        // Get the current user
        $user = Auth::user();
        if (! $user) {
            return AtkStockUsage::query()->whereRaw('0=1'); // Return empty query if no user
        }

        // Find approval flow steps that match the user's role and division
        $matchingStepIds = ApprovalFlowStep::whereHas('role', function ($query) use ($user) {
            $query->whereIn('id', $user->roles->pluck('id'));
        })
            ->where(function ($query) use ($user) {
                // Step is available for the user's own division OR for specific division
                $query
                    ->whereNull('division_id')
                    ->orWhere('division_id', $user->division_id);
            })
            ->pluck('id');

        // Get approval records that are pending and have steps matching the user's permissions
        $approvableIds = Approval::whereIn('current_step', $matchingStepIds)
            ->where('status', 'pending')
            ->where('approvable_type', (new AtkStockUsage)->getMorphClass()) // Ensure it's for ATK Stock Usages
            ->pluck('approvable_id');

        return AtkStockUsage::whereIn('id', $approvableIds)
            ->with([
                'requester',
                'division',
                'approval',
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
