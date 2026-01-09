<?php

namespace App\Filament\Resources\MonitoringEmails\Pages;

use App\Filament\Resources\MonitoringEmails\MonitoringEmailResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMonitoringEmails extends ManageRecords
{
    protected static string $resource = MonitoringEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for monitoring
        ];
    }
}
