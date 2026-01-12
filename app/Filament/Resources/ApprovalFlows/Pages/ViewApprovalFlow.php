<?php

namespace App\Filament\Resources\ApprovalFlows\Pages;

use App\Filament\Actions\DuplicateAction;
use App\Filament\Resources\ApprovalFlows\ApprovalFlowResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewApprovalFlow extends ViewRecord
{
    protected static string $resource = ApprovalFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->successNotificationTitle('Approval flow updated'),
            DuplicateAction::make(),
        ];
    }
}
