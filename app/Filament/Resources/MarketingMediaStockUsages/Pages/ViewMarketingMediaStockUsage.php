<?php

namespace App\Filament\Resources\MarketingMediaStockUsages\Pages;

use App\Filament\Actions\ApprovalAction;
use App\Filament\Actions\ResubmitAction;
use App\Filament\Resources\MarketingMediaStockUsages\MarketingMediaStockUsageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMarketingMediaStockUsage extends ViewRecord
{
    protected static string $resource = MarketingMediaStockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ApprovalAction::makeApprove(),
            ApprovalAction::makeReject(),
            ResubmitAction::make(),
        ];
    }
}
