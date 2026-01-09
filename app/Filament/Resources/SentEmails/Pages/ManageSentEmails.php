<?php

namespace App\Filament\Resources\SentEmails\Pages;

use App\Filament\Resources\SentEmails\SentEmailResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSentEmails extends ManageRecords
{
    protected static string $resource = SentEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for monitoring
        ];
    }
}
