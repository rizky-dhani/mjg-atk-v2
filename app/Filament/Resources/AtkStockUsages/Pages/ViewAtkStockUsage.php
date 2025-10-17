<?php

namespace App\Filament\Resources\AtkStockUsages\Pages;

use App\Filament\Actions\ApprovalAction;
use App\Filament\Resources\AtkStockUsages\AtkStockUsageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAtkStockUsage extends ViewRecord
{
    protected static string $resource = AtkStockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ApprovalAction::makeApprove(),
            ApprovalAction::makeReject(),
            ApprovalAction::makeResubmit(),
        ];
    }
}
