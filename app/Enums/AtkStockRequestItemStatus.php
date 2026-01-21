<?php

namespace App\Enums;

enum AtkStockRequestItemStatus: string
{
    case Pending = 'pending';
    case PartiallyReceived = 'partially_received';
    case FullyReceived = 'fully_received';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::PartiallyReceived => 'Diterima Sebagian',
            self::FullyReceived => 'Diterima Sepenuhnya',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::PartiallyReceived => 'info',
            self::FullyReceived => 'success',
        };
    }
}
