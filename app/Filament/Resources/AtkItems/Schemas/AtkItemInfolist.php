<?php

namespace App\Filament\Resources\AtkItems\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;

class AtkItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make("name"),
            TextEntry::make("description")->placeholder("-")->columnSpanFull(),
            TextEntry::make("category_id")->numeric(),
            TextEntry::make("unit"),
            TextEntry::make("created_at")->dateTime()->placeholder("-"),
            TextEntry::make("updated_at")->dateTime()->placeholder("-"),
        ]);
    }
}