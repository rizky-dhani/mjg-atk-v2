<?php

namespace App\Filament\Resources\AtkFulfillments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AtkFulfillmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('request_number')
                ->label('Nomor Permintaan')
                ->disabled(),
            TextInput::make('requester.name')
                ->label('Pemohon')
                ->disabled(),
            TextInput::make('division.name')
                ->label('Divisi')
                ->disabled(),
        ]);
    }
}